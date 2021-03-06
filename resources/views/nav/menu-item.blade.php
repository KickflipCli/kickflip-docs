<li class="pl-4">
    @if ($url = is_string($item) ? $item : $item->getUrl())
        {{-- Menu item with URL--}}
        <a href="{{ $url }}"
            class="{{ 'lvl' . $level }} {{ isActiveParent($page, $item) ? 'lvl' . $level . '-active' : '' }} {{ isActive($page, $url) ? 'active font-semibold text-blue-500' : '' }} nav-menu__item hover:text-blue-500"
        >
            <span>{{ $item->getLabel() }}</span>
        </a>
    @else
        {{-- Menu item without URL--}}
        <p class="nav-menu__item text-gray-600"><span>{{ $item->getLabel() }}</span></p>
    @endif

    @if ($item->hasChildren())
        {{-- Recursively handle children --}}
        @include('nav.menu', ['items' => $item->children ?? null, 'level' => ++$level])
    @endif
</li>
