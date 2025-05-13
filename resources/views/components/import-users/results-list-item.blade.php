@props(['hasAuthz', 'isExisting', 'id', 'isNew', 'username'])

<li>
    @if ($isNew)
        Created new user
    @elseif($isExisting && ! $hasAuthz)
        Added known user
    @elseif($isExisting && $hasAuthz)
        Skipped existing site user
    @endif
    {{$username}} ({{$id}})
</li>
