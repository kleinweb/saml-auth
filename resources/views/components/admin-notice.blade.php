<div @class([
    'notice',
    "notice-$type",
    'is-dismissible' => $isDismissible
])>
    <p>{{ $slot }}</p>
</div>
