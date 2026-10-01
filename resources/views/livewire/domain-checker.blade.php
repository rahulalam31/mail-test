<div class="mx-auto max-w-7xl space-y-6 p-6" @if ($bulkCheckId) wire:poll.2s="refreshBulk" @endif>
    {{-- Header --}}

    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            Domain Checking Tools
        </h1>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Check DNS records, blacklists and mail providers.
        </p>
    </div>


    {{-- Tabs --}}

    <div class="border-b border-gray-200 dark:border-gray-300">
        <nav class="-mb-px flex gap-6">

            <button type="button" wire:click="$set('activeTab', 'dns')" @class([
                'border-b-2 px-1 py-3 text-sm font-medium',
                'border-blue-600 text-blue-600' => $activeTab === 'dns',
                'border-transparent text-gray-500 hover:text-gray-700' =>
                    $activeTab !== 'dns',
            ])>
                Blacklist + DNS
            </button>

            <button type="button" wire:click="$set('activeTab', 'provider')"
                class="{{ $activeTab === 'provider'
                    ? 'border-black text-black'
                    : 'border-transparent text-gray-500 hover:text-gray-900' }} border-b-2 px-4 py-3 text-sm font-medium">
                Mail Provider
            </button>

        </nav>
    </div>


    {{-- Single Check --}}

    <div class="dark:border-white-700 dark:bg-white-800 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

        <div class="mb-5">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-black">
                Single Check
            </h2>

            <p class="text-sm text-gray-500">
                Enter a domain or email address.
            </p>
        </div>

        <form wire:submit="checkSingle" class="space-y-5">

            <div>
                <label class="mb-1 block text-sm font-medium">
                    Domain / Email
                </label>

                <input type="text" wire:model="input" placeholder="example.com or user@example.com"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">

                @error('input')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <button type="submit" wire:loading.attr="disabled" wire:target="checkSingle"
                class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">

                <span wire:loading.remove wire:target="checkSingle">
                    Check Domain
                </span>

                <span wire:loading wire:target="checkSingle">
                    Checking...
                </span>

            </button>

        </form>


        {{-- Error --}}

        @if ($singleError)
            <div class="mt-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                {{ $singleError }}
            </div>
        @endif

    </div>

    {{-- Single check error --}}
    @if ($singleError)
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <div class="font-semibold">Check failed</div>
            <div class="mt-1">
                {{ $singleError }}
            </div>
        </div>
    @endif
    {{-- Single Result --}}

    {{-- Single check result --}}
    @if ($singleResult && $activeTab === 'dns')
        <div class="mt-8 space-y-6">

            {{-- Header --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                    <div>
                        <div class="text-sm text-gray-500">
                            Domain
                        </div>

                        <div class="mt-1 text-2xl font-bold text-gray-900">
                            {{ $singleResult['domain'] }}
                        </div>

                        @if ($singleResult['input'] !== $singleResult['domain'])
                            <div class="mt-1 text-sm text-gray-500">
                                Input: {{ $singleResult['input'] }}
                            </div>
                        @endif
                    </div>


                    {{-- Blacklist status --}}
                    <div>
                        @php
                            $blacklistStatus = $singleResult['blacklist_status'] ?? 'unknown';
                        @endphp

                        @if ($blacklistStatus === 'clean')
                            <span
                                class="inline-flex rounded-full bg-green-100 px-4 py-2 text-sm font-semibold text-green-700">
                                ✓ Clean
                            </span>
                        @elseif ($blacklistStatus === 'listed')
                            <span
                                class="inline-flex rounded-full bg-red-100 px-4 py-2 text-sm font-semibold text-red-700">
                                ✕ Blacklisted
                            </span>
                        @else
                            <span
                                class="inline-flex rounded-full bg-yellow-100 px-4 py-2 text-sm font-semibold text-yellow-700">
                                ? Unknown
                            </span>
                        @endif
                    </div>

                </div>

            </div>


            {{-- Mail provider --}}
            <div class="grid gap-4 md:grid-cols-3">

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="text-sm text-gray-500">
                        Mail Provider
                    </div>

                    <div class="mt-2 text-lg font-semibold text-gray-900">
                        @switch($singleResult['mail_provider'] ?? 'not_detected')
                            @case('google')
                                Google Workspace
                            @break

                            @case('microsoft')
                                Microsoft 365
                            @break

                            @case('other')
                                Other Provider
                            @break

                            @default
                                Not Detected
                        @endswitch
                    </div>
                </div>


                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="text-sm text-gray-500">
                        MX Records
                    </div>

                    <div class="mt-2 text-lg font-semibold text-gray-900">
                        {{ count($singleResult['mx_records'] ?? []) }}
                    </div>
                </div>


                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="text-sm text-gray-500">
                        Nameservers
                    </div>

                    <div class="mt-2 text-lg font-semibold text-gray-900">
                        {{ count($singleResult['nameservers'] ?? []) }}
                    </div>
                </div>

            </div>


            {{-- DNS --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

                <h3 class="text-lg font-semibold text-gray-900">
                    DNS Records
                </h3>

                <div class="mt-6 grid gap-6 md:grid-cols-2">

                    {{-- A --}}
                    <div>
                        <div class="mb-2 font-medium text-gray-700">
                            A Records
                        </div>

                        @forelse ($singleResult['dns_records']['a'] ?? [] as $record)
                            <div class="rounded-lg bg-gray-50 px-3 py-2 text-sm">
                                {{ $record['ip'] ?? 'N/A' }}
                            </div>
                        @empty
                            <div class="text-sm text-gray-400">
                                No A records
                            </div>
                        @endforelse
                    </div>


                    {{-- AAAA --}}
                    <div>
                        <div class="mb-2 font-medium text-gray-700">
                            AAAA Records
                        </div>

                        @forelse ($singleResult['dns_records']['aaaa'] ?? [] as $record)
                            <div class="rounded-lg bg-gray-50 px-3 py-2 text-sm">
                                {{ $record['ipv6'] ?? 'N/A' }}
                            </div>
                        @empty
                            <div class="text-sm text-gray-400">
                                No AAAA records
                            </div>
                        @endforelse
                    </div>


                    {{-- MX --}}
                    <div class="md:col-span-2">
                        <div class="mb-2 font-medium text-gray-700">
                            MX Records
                        </div>

                        @forelse ($singleResult['mx_records'] ?? [] as $mx)
                            <div class="mb-2 flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 text-sm">
                                <span>
                                    {{ $mx['host'] ?? 'N/A' }}
                                </span>

                                <span class="text-gray-500">
                                    Priority {{ $mx['priority'] ?? '-' }}
                                </span>
                            </div>
                        @empty
                            <div class="text-sm text-gray-400">
                                No MX records
                            </div>
                        @endforelse
                    </div>


                    {{-- NS --}}
                    <div class="md:col-span-2">
                        <div class="mb-2 font-medium text-gray-700">
                            Nameservers
                        </div>

                        @forelse ($singleResult['nameservers'] ?? [] as $ns)
                            <div class="mb-2 rounded-lg bg-gray-50 px-3 py-2 text-sm">
                                {{ $ns['target'] ?? 'N/A' }}
                            </div>
                        @empty
                            <div class="text-sm text-gray-400">
                                No nameservers
                            </div>
                        @endforelse
                    </div>

                </div>

            </div>


            {{-- SPF / DMARC --}}
            <div class="grid gap-6 md:grid-cols-2">

                {{-- SPF --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900">
                            SPF
                        </h3>

                        @if (data_get($singleResult, 'spf.present'))
                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                Present
                            </span>
                        @else
                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                Missing
                            </span>
                        @endif
                    </div>

                    @if (data_get($singleResult, 'spf.value'))
                        <pre class="mt-4 overflow-x-auto rounded-lg bg-gray-50 p-4 text-xs">{{ data_get($singleResult, 'spf.value') }}</pre>
                    @endif

                </div>


                {{-- DMARC --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900">
                            DMARC
                        </h3>

                        @if (data_get($singleResult, 'dmarc.present'))
                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                Present
                            </span>
                        @else
                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                Missing
                            </span>
                        @endif
                    </div>

                    @if (data_get($singleResult, 'dmarc.value'))
                        <pre class="mt-4 overflow-x-auto rounded-lg bg-gray-50 p-4 text-xs">{{ data_get($singleResult, 'dmarc.value') }}</pre>
                    @endif

                </div>

            </div>


            {{-- DKIM --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

                <h3 class="font-semibold text-gray-900">
                    DKIM
                </h3>

                @forelse ($singleResult['dkim'] ?? [] as $dkim)
                    <div class="mt-4 rounded-lg bg-gray-50 p-4">

                        <div class="font-medium">
                            Selector:
                            {{ $dkim['selector'] }}
                        </div>

                        @foreach ($dkim['records'] ?? [] as $record)
                            <pre class="mt-2 overflow-x-auto text-xs">{{ $record }}</pre>
                        @endforeach

                    </div>
                @empty
                    <div class="mt-3 text-sm text-gray-400">
                        No DKIM record detected.
                        Provide a DKIM selector to check a specific selector.
                    </div>
                @endforelse

            </div>


            {{-- Blacklists --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

                <h3 class="font-semibold text-gray-900">
                    Blacklist Results
                </h3>

                @php
                    $listed = data_get($singleResult, 'blacklists.listed', []);
                @endphp

                @forelse ($listed as $item)
                    <div class="mt-3 rounded-lg border border-red-200 bg-red-50 p-4">

                        <div class="font-semibold text-red-700">
                            {{ $item['blacklist'] ?? 'Unknown blacklist' }}
                        </div>

                        <div class="mt-1 text-sm text-red-600">
                            IP: {{ $item['ip'] ?? 'Unknown' }}
                        </div>

                    </div>

                @empty

                    <div class="mt-3 text-sm text-green-600">
                        No blacklist listing detected.
                    </div>
                @endforelse

            </div>


            {{-- Provider evidence --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

                <h3 class="font-semibold text-gray-900">
                    Mail Provider Evidence
                </h3>

                @forelse ($singleResult['detection_evidence'] ?? [] as $evidence)
                    <div class="mt-2 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-700">
                        {{ $evidence }}
                    </div>
                @empty
                    <div class="mt-3 text-sm text-gray-400">
                        No detection evidence.
                    </div>
                @endforelse

            </div>


            {{-- Debug / raw result --}}
            <details class="rounded-xl border border-gray-200 bg-gray-50 p-4">

                <summary class="cursor-pointer font-medium text-gray-700">
                    Raw result
                </summary>

                <pre class="mt-4 overflow-x-auto text-xs">{{ json_encode($singleResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>

            </details>

        </div>
    @endif

    @if ($singleResult && $activeTab === 'provider')

        <div class="space-y-6">

            @php
                $provider = $singleResult['mail_provider'] ?? 'not_detected';
            @endphp


            {{-- Provider --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

                <div class="text-sm text-gray-500">
                    Detected mail provider
                </div>

                <div class="mt-2 flex items-center gap-3">

                    @if ($provider === 'google')
                        <span class="rounded-full bg-blue-100 px-4 py-2 text-sm font-semibold text-blue-700">
                            Google Workspace
                        </span>
                    @elseif ($provider === 'microsoft')
                        <span class="rounded-full bg-indigo-100 px-4 py-2 text-sm font-semibold text-indigo-700">
                            Microsoft 365
                        </span>
                    @elseif ($provider === 'other')
                        <span class="rounded-full bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700">
                            Other Provider
                        </span>
                    @else
                        <span class="rounded-full bg-yellow-100 px-4 py-2 text-sm font-semibold text-yellow-700">
                            Not Detected
                        </span>
                    @endif

                </div>

            </div>


            {{-- Evidence --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

                <h3 class="text-lg font-semibold text-gray-900">
                    Detection Evidence
                </h3>

                <div class="mt-4 space-y-2">

                    @forelse ($singleResult['detection_evidence'] ?? [] as $evidence)
                        <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-700">
                            {{ $evidence }}
                        </div>

                    @empty

                        <div class="text-sm text-gray-400">
                            No detection evidence available.
                        </div>
                    @endforelse

                </div>

            </div>


            {{-- MX --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

                <h3 class="text-lg font-semibold text-gray-900">
                    MX Records Used for Detection
                </h3>

                <div class="mt-4 overflow-hidden rounded-xl border border-gray-200">

                    <table class="min-w-full divide-y divide-gray-200">

                        <thead class="bg-gray-50">

                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                    Priority
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                    Mail Server
                                </th>
                            </tr>

                        </thead>

                        <tbody class="divide-y divide-gray-200 bg-white">

                            @forelse ($singleResult['mx_records'] ?? [] as $mx)
                                <tr>

                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        {{ $mx['priority'] ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3 font-mono text-sm text-gray-700">
                                        {{ $mx['host'] ?? '-' }}
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="2" class="px-4 py-6 text-center text-sm text-gray-400">
                                        No MX records found.
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    @endif

    {{-- Bulk Upload --}}

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">

        <div class="mb-5">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                Bulk Check
            </h2>

            <p class="text-sm text-gray-500">
                Upload a CSV or TXT file containing domains or email addresses.
            </p>
        </div>


        <form wire:submit="uploadBulk" class="space-y-4">

            <input type="file" wire:model="file" accept=".csv,.txt"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 text-sm">

            @error('file')
                <p class="text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror


            <div wire:loading wire:target="file" class="text-sm text-gray-500">
                Uploading...
            </div>


            <button type="submit" wire:loading.attr="disabled" wire:target="uploadBulk"
                class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-700 disabled:opacity-50">
                Upload & Start Checking
            </button>

        </form>

    </div>


    {{-- Bulk Progress --}}

    @if ($bulkCheck)
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

            <div class="flex items-center justify-between">

                <div>
                    <h3 class="font-semibold text-gray-900">
                        Bulk Check Progress
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        {{ $bulkCheck->filename }}
                    </p>
                </div>

                <div class="text-2xl font-bold text-gray-900">
                    {{ $bulkCheck->progress }}%
                </div>

            </div>


            <div class="mt-5 h-3 overflow-hidden rounded-full bg-gray-100">

                <div class="h-full rounded-full bg-black transition-all duration-500"
                    style="width: {{ $bulkCheck->progress }}%"></div>

            </div>


            <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-4">

                <div class="rounded-xl bg-gray-50 p-4">
                    <div class="text-xs text-gray-500">
                        Queued
                    </div>

                    <div class="mt-1 text-xl font-bold">
                        {{ $bulkCheck->queued }}
                    </div>
                </div>


                <div class="rounded-xl bg-gray-50 p-4">
                    <div class="text-xs text-gray-500">
                        Checking
                    </div>

                    <div class="mt-1 text-xl font-bold">
                        {{ $bulkCheck->checking }}
                    </div>
                </div>


                <div class="rounded-xl bg-gray-50 p-4">
                    <div class="text-xs text-gray-500">
                        Completed
                    </div>

                    <div class="mt-1 text-xl font-bold">
                        {{ $bulkCheck->completed }}
                    </div>
                </div>


                <div class="rounded-xl bg-gray-50 p-4">
                    <div class="text-xs text-gray-500">
                        Failed
                    </div>

                    <div class="mt-1 text-xl font-bold">
                        {{ $bulkCheck->failed }}
                    </div>
                </div>

            </div>

        </div>
    @endif


    {{-- Bulk Results --}}

    @if ($bulkCheckId)

        <div
            class="dark:border-white-700 dark:bg-white-800 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            {{-- Toolbar --}}

            <div
                class="flex flex-col gap-3 border-b border-gray-200 p-4 md:flex-row md:items-center md:justify-between">

                <div class="flex gap-2">
                    <select wire:model.live="filter" class="rounded-lg border-gray-300 text-sm">
                        <option value="all">All</option>
                        <option value="clean">Clean</option>
                        <option value="blacklisted">Blacklisted</option>
                        <option value="google">Google Workspace</option>
                        <option value="microsoft">Microsoft 365</option>
                        <option value="other">Other Provider</option>
                        <option value="not_detected">Not Detected</option>
                        <option value="failed">Failed</option>
                    </select>


                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search domain..."
                        class="rounded-lg border-gray-300 text-sm">

                </div>

                @if ($bulkCheckId)
                    <a href="{{ route('domain-checks.export', ['bulkCheckId' => $bulkCheckId]) }}"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                        Export Completed
                    </a>
                @endif

            </div>


            {{-- Table --}}

            <div class="overflow-x-auto">

                <table class="min-w-full text-left text-sm">

                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">

                        <tr>
                            <th class="px-4 py-3">
                                Domain
                            </th>

                            <th class="px-4 py-3">
                                Status
                            </th>

                            <th class="px-4 py-3">
                                Blacklist
                            </th>

                            <th class="px-4 py-3">
                                Provider
                            </th>

                            <th class="px-4 py-3">
                                MX
                            </th>

                            <th class="px-4 py-3">
                                Action
                            </th>
                        </tr>

                    </thead>


                    <tbody class="divide-y divide-gray-100">

                        @forelse($this->results as $check)
                            <tr wire:key="domain-check-{{ $check->id }}" class="hover:bg-gray-50">

                                <td class="whitespace-nowrap px-4 py-3 font-medium">
                                    {{ $check->domain }}
                                </td>


                                <td class="px-4 py-3">

                                    @if ($check->status === 'queued')
                                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs">
                                            Queued
                                        </span>
                                    @elseif($check->status === 'checking')
                                        <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs text-blue-700">
                                            Checking
                                        </span>
                                    @elseif($check->status === 'completed')
                                        <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs text-green-700">
                                            Completed
                                        </span>
                                    @else
                                        <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs text-red-700">
                                            Failed
                                        </span>
                                    @endif

                                </td>


                                <td class="px-4 py-3">

                                    @if ($check->blacklist_status === 'clean')
                                        <span class="text-green-600">
                                            Clean
                                        </span>
                                    @elseif($check->blacklist_status === 'listed')
                                        <span class="font-medium text-red-600">
                                            Listed
                                        </span>
                                    @elseif($check->blacklist_status === 'unknown')
                                        <span class="text-yellow-600">
                                            Unknown
                                        </span>
                                    @else
                                        <span class="text-gray-400">
                                            —
                                        </span>
                                    @endif

                                </td>


                                <td class="px-4 py-3">

                                    @switch($check->mail_provider)
                                        @case('google')
                                            <span class="text-blue-600">
                                                Google
                                            </span>
                                        @break

                                        @case('microsoft')
                                            <span class="text-blue-700">
                                                Microsoft
                                            </span>
                                        @break

                                        @case('other')
                                            <span>
                                                Other
                                            </span>
                                        @break

                                        @default
                                            <span class="text-gray-400">
                                                Not Detected
                                            </span>
                                    @endswitch

                                </td>


                                <td class="max-w-xs truncate px-4 py-3">

                                    @if ($check->status === 'completed')
                                        {{ collect($check->mx_records ?? [])->pluck('host')->implode(', ') }}
                                    @else
                                        <span class="text-gray-400">
                                            —
                                        </span>
                                    @endif

                                </td>


                                <td class="px-4 py-3">

                                    <button type="button" wire:click="selectCheck({{ $check->id }})"
                                        class="text-blue-600 hover:underline">
                                        Details
                                    </button>

                                </td>

                            </tr>

                            @empty

                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                                        No results found.
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        @endif


        {{-- Details Modal --}}

        @if ($showDetails && $this->selectedCheck)

            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                wire:click.self="closeDetails">

                <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-xl bg-white shadow-xl">

                    <div class="flex items-center justify-between border-b p-5">

                        <div>
                            <h2 class="text-lg font-semibold">
                                {{ $this->selectedCheck->domain }}
                            </h2>

                            <p class="text-sm text-gray-500">
                                {{ $this->selectedCheck->input }}
                            </p>
                        </div>

                        <button type="button" wire:click="closeDetails"
                            class="text-2xl text-gray-400 hover:text-gray-700">
                            &times;
                        </button>

                    </div>


                    <div class="space-y-6 p-5">

                        @if ($this->selectedCheck->status === 'failed')

                            <div class="rounded-lg bg-red-50 p-4 text-red-700">
                                {{ $this->selectedCheck->error }}
                            </div>
                        @else
                            <div class="grid gap-4 md:grid-cols-3">

                                <div class="rounded-lg border p-4">
                                    <div class="text-xs uppercase text-gray-500">
                                        Blacklist
                                    </div>

                                    <div class="mt-1 text-lg font-semibold">
                                        {{ ucfirst($this->selectedCheck->blacklist_status ?? '—') }}
                                    </div>
                                </div>


                                <div class="rounded-lg border p-4">
                                    <div class="text-xs uppercase text-gray-500">
                                        Mail Provider
                                    </div>

                                    <div class="mt-1 text-lg font-semibold">
                                        {{ ucfirst($this->selectedCheck->mail_provider ?? 'Not detected') }}
                                    </div>
                                </div>


                                <div class="rounded-lg border p-4">
                                    <div class="text-xs uppercase text-gray-500">
                                        Status
                                    </div>

                                    <div class="mt-1 text-lg font-semibold">
                                        {{ ucfirst($this->selectedCheck->status) }}
                                    </div>
                                </div>

                            </div>


                            {{-- MX --}}

                            <div>
                                <h3 class="mb-2 font-semibold">
                                    MX Records
                                </h3>

                                <div class="rounded-lg bg-gray-50 p-4">

                                    @forelse($this->selectedCheck->mx_records ?? []
                                                                as $mx)
                                        <div class="flex justify-between border-b py-2 last:border-0">

                                            <span>
                                                {{ $mx['host'] ?? '' }}
                                            </span>

                                            <span class="text-gray-500">
                                                Priority:
                                                {{ $mx['priority'] ?? '—' }}
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

                            <div>
                                <h3 class="mb-2 font-semibold">
                                    SPF
                                </h3>

                                <div class="rounded-lg bg-gray-50 p-4">

                                    @if (data_get($this->selectedCheck->spf, 'present'))
                                        <div class="mb-2 text-green-600">
                                            SPF detected
                                        </div>

                                        <code class="break-all text-sm">
                                            {{ data_get($this->selectedCheck->spf, 'value') }}
                                        </code>
                                    @else
                                        <span class="text-gray-500">
                                            SPF not detected.
                                        </span>
                                    @endif

                                </div>
                            </div>


                            {{-- DMARC --}}

                            <div>
                                <h3 class="mb-2 font-semibold">
                                    DMARC
                                </h3>

                                <div class="rounded-lg bg-gray-50 p-4">

                                    @if (data_get($this->selectedCheck->dmarc, 'present'))
                                        <div class="mb-2 text-green-600">
                                            DMARC detected
                                        </div>

                                        <code class="break-all text-sm">
                                            {{ data_get($this->selectedCheck->dmarc, 'value') }}
                                        </code>
                                    @else
                                        <span class="text-gray-500">
                                            DMARC not detected.
                                        </span>
                                    @endif

                                </div>
                            </div>


                            {{-- Blacklists --}}

                            <div>
                                <h3 class="mb-2 font-semibold">
                                    Blacklists
                                </h3>

                                <div class="space-y-2">

                                    @forelse(data_get(
                                                                    $this->selectedCheck->blacklists,
                                                                    'listed',
                                                                    []
                                                                ) as $listed)
                                        <div class="rounded-lg bg-red-50 p-3">

                                            <div class="font-medium text-red-700">
                                                {{ $listed['blacklist'] ?? '' }}
                                            </div>

                                            <div class="text-sm text-red-600">
                                                IP:
                                                {{ $listed['ip'] ?? '' }}
                                            </div>

                                        </div>

                                    @empty

                                        <div class="rounded-lg bg-green-50 p-3 text-green-700">
                                            No blacklist listing detected.
                                        </div>
                                    @endforelse

                                </div>
                            </div>


                            {{-- Nameservers --}}

                            <div>
                                <h3 class="mb-2 font-semibold">
                                    Nameservers
                                </h3>

                                <div class="rounded-lg bg-gray-50 p-4">

                                    @forelse($this->selectedCheck->nameservers ?? []
                                                                as $ns)
                                        <div class="py-1">
                                            {{ $ns['target'] ?? '' }}
                                        </div>

                                    @empty

                                        <span class="text-gray-500">
                                            No nameservers found.
                                        </span>
                                    @endforelse

                                </div>
                            </div>


                            {{-- DKIM --}}

                            <div>
                                <h3 class="mb-2 font-semibold">
                                    DKIM
                                </h3>

                                @forelse($this->selectedCheck->dkim ?? []
                                                            as $dkim)
                                    <div class="mb-2 rounded-lg bg-green-50 p-3">

                                        <div class="font-medium">
                                            Selector:
                                            {{ $dkim['selector'] }}
                                        </div>

                                        @foreach ($dkim['records'] ?? [] as $record)
                                            <code class="mt-1 block break-all text-xs">
                                                {{ $record }}
                                            </code>
                                        @endforeach

                                    </div>

                                @empty

                                    <div class="rounded-lg bg-gray-50 p-3 text-gray-500">
                                        No DKIM records detected for the checked selectors.
                                    </div>
                                @endforelse

                            </div>


                            {{-- All DNS --}}

                            <div>
                                <h3 class="mb-2 font-semibold">
                                    All DNS Records
                                </h3>

                                <pre class="overflow-x-auto rounded-lg bg-gray-900 p-4 text-xs text-white">{{ json_encode($this->selectedCheck->dns_records, JSON_PRETTY_PRINT) }}</pre>
                            </div>

                        @endif

                    </div>

                </div>

            </div>

        @endif

    </div>
