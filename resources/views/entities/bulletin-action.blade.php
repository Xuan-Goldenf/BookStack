@php
 $bulletinRoles = $entity->bulletinRoles();
 $roles = user()->roles->pluck('description', 'id')->toArray();
@endphp
<div class="toggle-switch-list dual-column-content">
<form action="{{ url('/bulletins/toggle') }}" method="POST">
    {{ csrf_field() }}
    <input type="hidden" name="type" value="{{ get_class($entity) }}">
    <input type="hidden" name="id" value="{{ $entity->id }}">
    <button type="submit" class="icon-list-item text-primary">
        <span>@icon(count($bulletinRoles)>0 ? 'star' : 'star-outline')</span>
        <span>{{ trans('common.bulletin') }}</span>
    </button>
        @foreach ($roles as $id => $description)
        <div>
        @include('form.custom-checkbox', [
            'name' => 'roles[]',
            'label' => $description,
            'value' => $id,
            'checked' => in_array($id, $bulletinRoles)
        ])
        </div>
    @endforeach
</form>
</div>