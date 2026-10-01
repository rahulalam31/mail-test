<div class="space-y-5">

    {{-- Summary --}}

    <div class="grid gap-4 md:grid-cols-4">

        <div class="rounded-xl border bg-white p-5">

            <div class="text-xs uppercase tracking-wide text-gray-500">
                Domain
            </div>

            <div class="mt-2 font-semibold">
                {{ $result['domain'] }}
            </div>

        </div>

        <div class="rounded-xl border bg-white p-5">

            <div class="text-xs uppercase tracking-wide text-gray-500">
                Blacklist
            </div>

            <div class="mt-2 font-semibold">

                @if ($result['blacklist_status'] === 'clean')

                    <span class="text-green-600">
                        Clean
                    </span>

                @elseif ($result['blacklist_status'] === 'listed')

                    <span class="text-red-600">
                        Listed
                    </span>

                @else

                    <span class="text-yellow-600">
                        Unknown
                    </span>

                @endif

            </div>

        </div>

        <div class="rounded-xl border bg-white p-5">

            <div class="text-xs uppercase tracking-wide text-gray-500">
                MX
            </div>

            <div class="mt-2 font-semibold">
                {{ count($result['mx_records']) }}
                records
            </div>

        </div>

        <div class="rounded-xl border bg-white p-5">

            <div class="text-xs uppercase tracking-wide text-gray-500">
                Provider
            </div>

            <div class="mt-2 font-semibold">

                @switch($result['mail_provider'])

                    @case('google')
                        Google Workspace
                        @break

                    @case('microsoft')
                        Microsoft 365
                        @break

                    @case('other')
                        Other
                        @break

                    @default
                        Not Detected

                @endswitch

            </div>

        </div>

    </div>


    {{-- MX --}}

    <div class="rounded-xl border bg-white p-6">

        <h2 class="font-semibold">
            MX Records
        </h2>

        <div class="mt-4 divide-y">

            @forelse ($result['mx_records'] as $record)

                <div class="flex justify-between py-3">

                    <span>
                        {{ $record['host'] }}
                    </span>

                    <span class="text-gray-500">
                        Priority {{ $record['priority'] }}
                    </span>

                </div>

            @empty

                <p class="py-3 text-sm text-gray-500">
                    No MX records detected.
                </p>

            @endforelse

        </div>

    </div>


    {{-- SPF + DMARC --}}

    <div class="grid gap-5 md:grid-cols-2">

        <div class="rounded-xl border bg-white p-6">

            <h2 class="font-semibold">
                SPF
            </h2>

            @if ($result['spf']['present'])

                <div class="mt-3 rounded-lg bg-green-50 p-4">

                    <div class="text-sm font-medium text-green-700">
                        Present
                    </div>

                    <code class="mt-2 block break-all text-xs">
                        {{ $result['spf']['value'] }}
                    </code>

                </div>

            @else

                <p class="mt-3 text-sm text-gray-500">
                    No SPF record detected.
                </p>

            @endif

        </div>


        <div class="rounded-xl border bg-white p-6">

            <h2 class="font-semibold">
                DMARC
            </h2>

            @if ($result['dmarc']['present'])

                <div class="mt-3 rounded-lg bg-green-50 p-4">

                    <div class="text-sm font-medium text-green-700">
                        Present
                    </div>

                    <code class="mt-2 block break-all text-xs">
                        {{ $result['dmarc']['value'] }}
                    </code>

                </div>

            @else

                <p class="mt-3 text-sm text-gray-500">
                    No DMARC record detected.
                </p>

            @endif

        </div>

    </div>


    {{-- DKIM --}}

    <div class="rounded-xl border bg-white p-6">

        <h2 class="font-semibold">
            DKIM
        </h2>

        @forelse ($result['dkim'] as $dkim)

            <div class="mt-4 rounded-lg bg-gray-50 p-4">

                <div class="font-medium">
                    Selector:
                    {{ $dkim['selector'] }}
                </div>

                <code class="mt-2 block break-all text-xs">
                    {{ implode(
                        "\n",
                        $dkim['records']
                    ) }}
                </code>

            </div>

        @empty

            <p class="mt-3 text-sm text-gray-500">
                No DKIM record detected for the configured selectors.
            </p>

        @endforelse

    </div>


    {{-- Nameservers --}}

    <div class="rounded-xl border bg-white p-6">

        <h2 class="font-semibold">
            Nameservers
        </h2>

        <div class="mt-4 space-y-2">

            @forelse ($result['nameservers'] as $record)

                <div class="rounded-lg bg-gray-50 px-4 py-3">
                    {{ $record['target'] ?? '—' }}
                </div>

            @empty

                <p class="text-sm text-gray-500">
                    No nameservers detected.
                </p>

            @endforelse

        </div>

    </div>


    {{-- Blacklists --}}

    <div class="rounded-xl border bg-white p-6">

        <h2 class="font-semibold">
            Blacklist Results
        </h2>

        @if (!empty($result['blacklists']['listed']))

            <div class="mt-4 space-y-3">

                @foreach (
                    $result['blacklists']['listed']
                    as $blacklist
                )

                    <div class="rounded-lg bg-red-50 p-4">

                        <div class="font-medium text-red-700">
                            {{ $blacklist['blacklist'] }}
                        </div>

                        <div class="mt-1 text-sm">
                            IP:
                            {{ $blacklist['ip'] }}
                        </div>

                    </div>

                @endforeach

            </div>

        @elseif (
            !empty($result['blacklists']['errors'])
        )

            <div class="mt-4 rounded-lg bg-yellow-50 p-4">

                <p class="text-sm text-yellow-800">
                    Some DNSBL checks could not be completed.
                    The blacklist status is therefore marked
                    as unknown.
                </p>

            </div>

        @else

            <div class="mt-4 rounded-lg bg-green-50 p-4">

                <p class="text-sm text-green-700">
                    No configured DNSBL listed the resolved IP addresses.
                </p>

            </div>

        @endif

    </div>


    {{-- All DNS --}}

    <details class="rounded-xl border bg-white">

        <summary class="cursor-pointer p-6 font-semibold">
            All DNS Records
        </summary>

        <pre class="overflow-x-auto border-t bg-gray-50 p-6 text-xs">{{ json_encode(
    $result['dns_records'],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
) }}</pre>

    </details>

</div>
