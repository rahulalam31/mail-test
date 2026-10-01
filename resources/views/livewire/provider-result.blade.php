<div class="space-y-5">

    <div class="rounded-xl border bg-white p-6">

        <div class="text-sm text-gray-500">
            Domain
        </div>

        <div class="mt-1 text-xl font-semibold">
            {{ $result['domain'] }}
        </div>

    </div>


    <div class="grid gap-5 md:grid-cols-2">

        <div class="rounded-xl border bg-white p-6">

            <div class="text-sm text-gray-500">
                Provider
            </div>

            <div class="mt-2 text-2xl font-semibold">

                @switch($result['mail_provider'])

                    @case('google')

                        Google Workspace

                        @break

                    @case('microsoft')

                        Microsoft 365

                        @break

                    @case('other')

                        Other Mail Provider

                        @break

                    @default

                        Not Detected

                @endswitch

            </div>

        </div>


        <div class="rounded-xl border bg-white p-6">

            <div class="text-sm text-gray-500">
                Status
            </div>

            <div class="mt-2 text-2xl font-semibold">

                @if ($result['mail_provider'] === 'not_detected')

                    <span class="text-gray-500">
                        Not Detected
                    </span>

                @else

                    <span class="text-green-600">
                        Detected
                    </span>

                @endif

            </div>

        </div>

    </div>


    <div class="rounded-xl border bg-white p-6">

        <h2 class="font-semibold">
            Detection Evidence
        </h2>

        <div class="mt-4 space-y-2">

            @foreach (
                $result['detection_evidence']
                as $evidence
            )

                <div class="rounded-lg bg-gray-50 p-4 text-sm">
                    {{ $evidence }}
                </div>

            @endforeach

        </div>

    </div>


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

</div>
