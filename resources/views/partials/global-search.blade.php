{{-- Top bar: opens search modal (suggestions); results stay on /account/search?q=… --}}
@if($currentFamily)
{{-- Always show on admin + app nav (was hidden md:flex on admin, which hid family search on small screens). --}}
<div class="flex flex-1 min-w-0 items-center gap-2 mx-2 lg:mx-4">
 <button
  type="button"
  id="fl-global-search-open"
  class="fl-topbar-search-btn"
  data-fl-open-search-modal
  data-suggestions-url="{{ route('families.search.suggestions') }}"
  data-search-url="{{ route('families.search.index') }}"
  aria-haspopup="dialog"
 >
  <span class="fl-topbar-search-btn__icon-wrap" aria-hidden="true">
   <i class="ki-filled ki-magnifier fl-topbar-search-btn__icon"></i>
  </span>
  <span class="fl-topbar-search-btn__label">{{ __('Search') }}</span>
 </button>
 @if(request()->routeIs('admin.*'))
  <div class="shrink-0 w-full max-w-[220px] hidden sm:block relative" id="fl-admin-user-search-wrap">
   <form method="get" action="{{ route('admin.users.index') }}" class="w-full relative" role="search">
    <span class="absolute start-2.5 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" aria-hidden="true">
     <i class="ki-filled ki-people text-lg"></i>
    </span>
    <input
     type="search"
     name="search"
     class="kt-input w-full ps-9 pe-3 py-2 text-sm"
     placeholder="{{ __('Users…') }}"
     autocomplete="off"
     value="{{ request('search') }}"
    />
   </form>
  </div>
 @endif
</div>
@elseif(request()->routeIs('admin.*') && auth()->check())
{{-- Platform admin without a family: search users only --}}
<div class="flex flex-1 min-w-0 max-w-sm mx-2 lg:mx-4 relative" id="fl-admin-user-search-wrap-only">
 <form method="get" action="{{ route('admin.users.index') }}" class="w-full relative" role="search">
  <span class="absolute start-2.5 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" aria-hidden="true">
   <i class="ki-filled ki-magnifier text-lg"></i>
  </span>
  <input
   type="search"
   name="search"
   class="kt-input w-full ps-9 pe-3 py-2 text-sm"
   placeholder="{{ __('Search users…') }}"
   autocomplete="off"
   value="{{ request('search') }}"
  />
 </form>
</div>
@endif
