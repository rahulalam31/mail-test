<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white-700 dark:bg-white-800">

    <div class="mb-6 flex items-center justify-between">

        <div>
            <h2 class="text-lg font-semibold">
                Result
            </h2>

            <p class="text-sm text-gray-500">
                {{ $result['domain'] }}
            </p>
        </div>

        @if ($result['blacklist_status'] === 'clean')
            <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-700">
                Clean
            </span>
        @elseif($result['blacklist_status'] === 'listed')
            <span class="rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-700">
                Listed
            </span>
        @else
            <span class="rounded-full bg-yellow-100 px-3 py-1 text-sm font-medium text-yellow-700">
                Unknown
            </span>
        @endif

    </div>


    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">

        {{-- Provider --}}

        <div class="rounded-lg border p-4">

            <div class="text-xs uppercase text-gray-500">
                Mail Provider
            </div>

            <div class="mt-1 text-lg font-semibold">
                {{ match ($result['mail_provider']) {
                    'google' => 'Google Workspace',
                    'microsoft' => 'Microsoft 365',
                    'other' => 'Other',
                    default => 'Not Detected',
                } }}
            </div>

        </div>


        {{-- MX --}}

        <div class="rounded-lg border p-4">

            <div class="text-xs uppercase text-gray-500">
                MX Records
            </div>

            <div class="mt-1 text-lg font-semibold">
                {{ count($result['mx_records'] ?? []) }}
            </div>

        </div>


        {{-- SPF --}}

        <div class="rounded-lg border p-4">

            <div class="text-xs uppercase text-gray-500">
                SPF
            </div>

            <div class="mt-1 text-lg font-semibold">

                {{ data_get($result, 'spf.present') ? 'Present' : 'Not Found' }}

            </div>

        </div>


        {{-- DMARC --}}

        <div class="rounded-lg border p-4">

            <div class="text-xs uppercase text-gray-500">
                DMARC
            </div>

            <div class="mt-1 text-lg font-semibold">

                {{ data_get($result, 'dmarc.present') ? 'Present' : 'Not Found' }}

            </div>

        </div>

    </div>


    {{-- MX --}}

    <div class="mt-6">

        <h3 class="mb-2 font-semibold">
            MX Records
        </h3>

        <div class="rounded-lg bg-gray-50 p-4">

            @forelse($result['mx_records'] ?? [] as $mx)
                <div class="flex justify-between border-b py-2 last:border-0">

                    <span>
                        {{ $mx['host'] ?? '' }}
                    </span>

                    <span class="text-gray-500">
                        {{ $mx['priority'] ?? '' }}
                    </span>

                </div>

            @empty

                <span class="text-gray-500">
                    No MX records found.
                </span>
            @endforelse

        </div>

    </div>


    {{-- SPF --}}

    <div class="mt-6">

        <h3 class="mb-2 font-semibold">
            SPF
        </h3>

        @if (data_get($result, 'spf.present'))
            <pre class="overflow-x-auto rounded-lg bg-gray-900 p-4 text-xs text-white">{{ data_get($result, 'spf.value') }}</pre>
        @else
            <div class="rounded-lg bg-gray-50 p-4 text-gray-500">
                SPF not detected.
            </div>
        @endif

    </div>


    {{-- DMARC --}}

    <div class="mt-6">

        <h3 class="mb-2 font-semibold">
            DMARC
        </h3>

        @if (data_get($result, 'dmarc.present'))
            <pre class="overflow-x-auto rounded-lg bg-gray-900 p-4 text-xs text-white">{{ data_get($result, 'dmarc.value') }}</pre>
        @else
            <div class="rounded-lg bg-gray-50 p-4 text-gray-500">
                DMARC not detected.
            </div>
        @endif

    </div>


    {{-- Provider Evidence --}}

    <div class="mt-6">

        <h3 class="mb-2 font-semibold">
            Detection Evidence
        </h3>

        <ul class="list-disc space-y-1 pl-5 text-sm text-gray-600">

            @foreach ($result['detection_evidence'] ?? [] as $evidence)
                <li>
                    {{ $evidence }}
                </li>
            @endforeach

        </ul>

    </div>

</div>
