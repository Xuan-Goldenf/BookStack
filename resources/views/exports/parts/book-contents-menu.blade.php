@if(count($children) > 0)
    <ul class="contents">
        @foreach($children as $bookChild)
            <li><a href="#{{$bookChild->getType()}}-{{$bookChild->id}}" style="font-size: 20px;">{{ $bookChild->name }}</a></li>
            @if($bookChild->isA('chapter') && count($bookChild->visible_pages) > 0)
                @include('exports.parts.chapter-contents-menu', ['pages' => $bookChild->visible_pages])
            @endif
        @endforeach
    </ul>
@endif