@if (count($pages) > 0)
        <ul class="contents" style="font-size: 20px;">
            @foreach($pages as $page)
                <li><a href="#page-{{$page->id}}" style="font-size: 20px;">{{ $page->name }}</a></li>
            @endforeach
        </ul>
@endif