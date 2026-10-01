<div>

    <div class="flex items-center justify-between border-b p-6">

        <div>
            <h2 class="text-lg font-semibold">
                {{ $check->domain }}
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Domain check details
            </p>
        </div>

        <button
            type="button"
            wire:click="closeDetails"
            class="rounded-lg px-3 py-2 text-gray-500
                hover:bg-gray-100"
        >
            ✕
        </button>

    </div>


    <div class="space-y-6 p-6">

        {{-- Status --}}

        <div class="grid gap-4 md:grid-cols-3">

            <div class="rounded-lg bg-gray-50 p-4">

                <div class="text-xs text-gray-500">
                    Status
                </div>

                <div class="mt-1 font-semibold">
                    {{ ucfirst($check->status) }}
                </div>

            </div>

            <div class="rounded-lg bg-gray-50 p-4">

                <div class="text-xs text-gray-500">
                    Blacklist
                </div>

                <div class="mt-1 font-semibold">
                    {{ ucfirst(
                        $check->blacklist_status ?? '—'
                    ) }}
                </div>

            </div>

            <div class="rounded-lg bg-gray-50 p-4">

                <div class="text-xs text-gray-500">
                    Provider
                </div>

                <div class="mt-1 font-semibold">
                    {{ ucfirst(
                        $check->mail_provider ?? '—'
                    ) }}
                </div>

            </div>

        </div>


        @if ($check->error)

            <div class="rounded-lg bg-red-50 p-4">

                <div class="font-medium text-red-700">
                    Error
                </div>

                <div class="mt-1 text-sm text-red-600">
                    {{ $check->error }}
                </div>

            </div>

        @endif


        {{-- MX --}}

        <div>

            <h3 class="font-semibold">
                MX Records
            </h3>

            <pre class="mt-3 overflow-x-auto rounded-lg
                bg-gray-50 p-4 text-xs">{{ json_encode(
    $check->mx_records,
    JSON_PRETTY_PRINT
) }}</pre>

        </div>


        {{-- SPF --}}

        <div>

            <h3 class="font-semibold">
                SPF
            </h3>

            <pre class="mt-3 overflow-x-auto rounded-lg
                bg-gray-50 p-4 text-xs">{{ json_encode(
    $check->spf,
    JSON_PRETTY_PRINT
) }}</pre>

        </div>


        {{-- DMARC --}}

        <div>

            <h3 class="font-semibold">
                DMARC
            </h3>

            <pre class="mt-3 overflow-x-auto rounded-lg
                bg-gray-50 p-4 text-xs">{{ json_encode(
    $check->dmarc,
    JSON_PRETTY_PRINT
) }}</pre>

        </div>


        {{-- DNS --}}

        <details>

            <summary class="cursor-pointer font-semibold">
                All DNS Records
            </summary>

            <pre class="mt-3 overflow-x-auto rounded-lg
                bg-gray-50 p-4 text-xs">{{ json_encode(
    $check->dns_records,
    JSON_PRETTY_PRINT
) }}</pre>

        </details>


        {{-- Blacklists --}}

        <details>

            <summary class="cursor-pointer font-semibold">
                Blacklist Details
            </summary>

            <pre class="mt-3 overflow-x-auto rounded-lg
                bg-gray-50 p-4 text-xs">{{ json_encode(
    $check->blacklists,
    JSON_PRETTY_PRINT
) }}</pre>

        </details>

    </div>

</div>
