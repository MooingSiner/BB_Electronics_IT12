@php($helpTopics = \App\Support\HelpGuide::topics($helpRole))
@php($helpGuides = \App\Support\HelpGuide::guides($helpRole))
<div x-data="{ open: false, tab: 0 }" x-cloak x-show="open" @open-help.window="open = true; tab = 0" @keydown.escape.window="open = false"
     class="fixed inset-0 z-[70] flex items-center justify-center p-3 sm:p-6" role="dialog" aria-modal="true" aria-label="Help guide">
    <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-5xl max-h-full flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200">
            <h2 class="text-base font-semibold text-gray-900">Help guide</h2>
            <button type="button" @click="open = false" class="text-gray-500 hover:text-gray-900 text-2xl leading-none" aria-label="Close help">&times;</button>
        </div>
        <div class="flex flex-col md:flex-row min-h-0 flex-1">
            <div class="md:w-56 flex-shrink-0 flex md:flex-col gap-1 overflow-x-auto md:overflow-y-auto p-2 border-b md:border-b-0 md:border-r border-gray-200 bg-gray-50">
                @foreach ($helpTopics as $i => $topic)
                    <button type="button" @click="tab = {{ $i }}"
                            :class="tab === {{ $i }} ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-200'"
                            class="whitespace-nowrap text-left text-sm px-3 py-2 rounded-md">{{ $topic['title'] }}</button>
                @endforeach
                <div class="hidden md:block text-xs uppercase tracking-wider text-gray-400 px-3 pt-3">Step by step</div>
                @foreach ($helpGuides as $g => $guide)
                    <button type="button" @click="tab = {{ count($helpTopics) + $g }}"
                            :class="tab === {{ count($helpTopics) + $g }} ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-200'"
                            class="whitespace-nowrap text-left text-sm px-3 py-2 rounded-md">{{ $guide['title'] }}</button>
                @endforeach
            </div>
            <div class="flex-1 overflow-y-auto p-5" x-ref="body" x-effect="tab; $refs.body && ($refs.body.scrollTop = 0)">
                @foreach ($helpTopics as $i => $topic)
                    <div x-show="tab === {{ $i }}" @if ($i > 0) x-cloak @endif>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $topic['title'] }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $topic['summary'] }}</p>
                        <ul class="mt-3 space-y-1.5 text-sm text-gray-700 list-disc pl-5">
                            @foreach ($topic['points'] as $point)
                                <li>{{ $point }}</li>
                            @endforeach
                        </ul>
                        <img src="{{ asset('help/'.$topic['image'].'.jpg') }}" alt="{{ $topic['title'] }} screen" loading="lazy"
                             class="mt-4 w-full rounded-lg border border-gray-200" onerror="this.style.display='none'">
                    </div>
                @endforeach
                @foreach ($helpGuides as $g => $guide)
                    <div x-show="tab === {{ count($helpTopics) + $g }}" x-cloak>
                        <h3 class="text-lg font-semibold text-gray-900">How to: {{ $guide['title'] }}</h3>
                        <ol class="mt-4 space-y-6">
                            @foreach ($guide['steps'] as $n => $step)
                                <li>
                                    <div class="flex items-start gap-3">
                                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-gray-900 text-white text-xs font-semibold flex items-center justify-center">{{ $n + 1 }}</span>
                                        <p class="text-sm text-gray-700">{{ $step['text'] }}</p>
                                    </div>
                                    <img src="{{ asset('help/steps/'.$step['image'].'.jpg') }}" alt="Step {{ $n + 1 }}: {{ $guide['title'] }}" loading="lazy"
                                         class="mt-2 w-full rounded-lg border border-gray-200" onerror="this.style.display='none'">
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
