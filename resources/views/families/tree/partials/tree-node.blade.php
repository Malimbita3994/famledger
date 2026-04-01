@if(count($nodes) > 0)
    @if(count($nodes) === 1)
        <!-- Single node -->
        <div class="tree-node">
            <div class="tree-person-card">
                <div class="tree-person-avatar">
                    @if($nodes[0]['avatar'])
                        <img src="{{ $nodes[0]['avatar'] }}" alt="{{ $nodes[0]['name'] }}" class="avatar-image">
                    @else
                        <div class="avatar-placeholder">
                            <span class="avatar-initial">{{ substr($nodes[0]['name'], 0, 1) }}</span>
                        </div>
                    @endif
                </div>
                <div class="tree-person-info">
                    <h4 class="person-name">{{ $nodes[0]['name'] }}</h4>
                    <span class="person-role">{{ $nodes[0]['role'] }}</span>
                    @if(!empty($nodes[0]['spouses']))
                        <div class="mt-1.5 text-xs text-slate-500">
                            <span class="font-medium text-slate-600">{{ __('Spouse') }}:</span>
                            {{ collect($nodes[0]['spouses'])->pluck('name')->implode(', ') }}
                        </div>
                    @endif
                    @if(!empty($nodes[0]['siblings']))
                        <div class="mt-1 text-xs text-slate-500">
                            <span class="font-medium text-slate-600">{{ __('Sibling') }}:</span>
                            {{ collect($nodes[0]['siblings'])->pluck('name')->implode(', ') }}
                        </div>
                    @endif
                </div>
                <div class="tree-person-decoration">
                    <div class="decoration-line"></div>
                </div>
            </div>
            @if(count($nodes[0]['children']) > 0)
                <div class="tree-connector">
                    <div class="connector-line"></div>
                    <div class="connector-dot"></div>
                </div>
                <div class="tree-children">
                    @include('families.tree.partials.tree-node', ['nodes' => $nodes[0]['children'], 'level' => $level + 1])
                </div>
            @endif
        </div>
    @else
        <!-- Multiple siblings -->
        <div class="tree-siblings">
            @foreach($nodes as $node)
                <div class="tree-node">
                    <div class="tree-person-card">
                        <div class="tree-person-avatar">
                            @if($node['avatar'])
                                <img src="{{ $node['avatar'] }}" alt="{{ $node['name'] }}" class="avatar-image">
                            @else
                                <div class="avatar-placeholder">
                                    <span class="avatar-initial">{{ substr($node['name'], 0, 1) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="tree-person-info">
                            <h4 class="person-name">{{ $node['name'] }}</h4>
                            <span class="person-role">{{ $node['role'] }}</span>
                            @if(!empty($node['spouses']))
                                <div class="mt-1.5 text-xs text-slate-500">
                                    <span class="font-medium text-slate-600">{{ __('Spouse') }}:</span>
                                    {{ collect($node['spouses'])->pluck('name')->implode(', ') }}
                                </div>
                            @endif
                            @if(!empty($node['siblings']))
                                <div class="mt-1 text-xs text-slate-500">
                                    <span class="font-medium text-slate-600">{{ __('Sibling') }}:</span>
                                    {{ collect($node['siblings'])->pluck('name')->implode(', ') }}
                                </div>
                            @endif
                        </div>
                        <div class="tree-person-decoration">
                            <div class="decoration-line"></div>
                        </div>
                    </div>
                    @if(count($node['children']) > 0)
                        <div class="tree-connector">
                            <div class="connector-line"></div>
                            <div class="connector-dot"></div>
                        </div>
                        <div class="tree-children">
                            @include('families.tree.partials.tree-node', ['nodes' => $node['children'], 'level' => $level + 1])
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
@endif