<?php

namespace App\Services;

use App\Models\FamilyMember;
use App\Models\FamilyRelationship;
use Illuminate\Support\Collection;

class FamilyTreeBuilder
{
    /**
     * Build hierarchical root nodes for parent/child edges; attach spouse/sibling labels for display.
     *
     * @param  iterable<FamilyMember>  $members
     * @param  iterable<FamilyRelationship>  $relationships
     * @return array<int, array<string, mixed>>
     */
    public function buildRoots(iterable $members, iterable $relationships): array
    {
        $relationships = $relationships instanceof Collection ? $relationships : collect($relationships);

        $nodes = [];
        foreach ($members as $member) {
            $nodes[$member->user_id] = [
                'id' => $member->user_id,
                'name' => $member->user->name,
                'avatar' => $member->user->avatar_url ?? null,
                'role' => $member->role->name ?? 'Member',
                'children' => [],
                'spouses' => [],
                'siblings' => [],
            ];
        }

        foreach ($relationships as $rel) {
            $a = $rel->user_id;
            $b = $rel->related_user_id;
            if (! isset($nodes[$a], $nodes[$b])) {
                continue;
            }
            if ($rel->type === 'parent') {
                $nodes[$a]['children'][] = &$nodes[$b];
            } elseif ($rel->type === 'child') {
                $nodes[$b]['children'][] = &$nodes[$a];
            } elseif ($rel->type === 'spouse') {
                $this->appendUnique($nodes[$a]['spouses'], $this->personSnapshot($nodes[$b]));
                $this->appendUnique($nodes[$b]['spouses'], $this->personSnapshot($nodes[$a]));
            } elseif ($rel->type === 'sibling') {
                $this->appendUnique($nodes[$a]['siblings'], $this->personSnapshot($nodes[$b]));
                $this->appendUnique($nodes[$b]['siblings'], $this->personSnapshot($nodes[$a]));
            }
        }

        $roots = array_filter($nodes, function (array $node) use ($relationships) {
            foreach ($relationships as $rel) {
                if ($rel->type === 'parent' && (int) $rel->related_user_id === (int) $node['id']) {
                    return false;
                }
                if ($rel->type === 'child' && (int) $rel->user_id === (int) $node['id']) {
                    return false;
                }
            }

            return true;
        });

        return array_values($roots);
    }

    /**
     * @param  array<string, mixed>  $node
     * @return array{id:int,name:string,avatar:?string,role:string}
     */
    private function personSnapshot(array $node): array
    {
        return [
            'id' => $node['id'],
            'name' => $node['name'],
            'avatar' => $node['avatar'] ?? null,
            'role' => $node['role'] ?? 'Member',
        ];
    }

    /**
     * @param  array<int, array{id:int,name:string,avatar:?string,role:string}>  $list
     * @param  array{id:int,name:string,avatar:?string,role:string}  $snap
     */
    private function appendUnique(array &$list, array $snap): void
    {
        foreach ($list as $s) {
            if ((int) $s['id'] === (int) $snap['id']) {
                return;
            }
        }
        $list[] = $snap;
    }
}
