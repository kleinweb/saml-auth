<div class="wrap">
    <h1>Bulk Add Users from Canvas</h1>

    @if ($notice)
        <x-kleinweb-auth::admin-notice
            :type="$notice->type->value"
            :is-dismissible="$notice->isDismissible"
        >
            {{ $notice->message }}
        </x-kleinweb-auth::admin-notice>
    @endif

    @if ($results)
        <x-kleinweb-auth::import-users.results-list :users="$results->data" />
    @else
        <p>
            Use this page to automatically add users to the current site
            based on a CSV file exported from Canvas.
            All newly-added users will be assigned the Contributor role.
        </p>

        <h2>Instructions</h2>

        <x-kleinweb-auth::import-users.instructions />

        <x-kleinweb-auth::import-users.form />
    @endif
</div>
