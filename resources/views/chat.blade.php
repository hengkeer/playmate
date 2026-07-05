@extends('layouts.app')

@section('title', 'Group Chat — ' . $event->title)

@section('content')
<div class="max-w-3xl mx-auto flex flex-col" style="height:calc(100vh - 140px)">

    {{-- Header --}}
    <div class="flex items-center gap-4 pb-5" style="border-bottom:1px solid rgb(var(--border-base))">
        <a href="{{ route('events.show', $event) }}"
           class="font-display text-[0.65rem] tracking-widest text-white/30 hover:text-accent uppercase transition shrink-0">
            ← Event
        </a>

        {{-- Sport icon badge --}}
        <div class="h-10 w-10 flex items-center justify-center shrink-0"
             style="border:1px solid rgba(249,115,22,0.3);background:rgba(249,115,22,0.07)">
            <span class="font-display text-accent text-[0.6rem] tracking-widest uppercase">
                {{ substr($event->sport?->name ?? 'ALL', 0, 3) }}
            </span>
        </div>

        <div class="flex-1 min-w-0">
            <h2 class="font-display text-xl uppercase text-white truncate" style="font-weight:600">
                {{ $event->title }}
            </h2>
            <p class="font-display text-[0.58rem] tracking-widest text-white/30 uppercase mt-0.5">
                {{ $event->sport?->name ?? 'Any Sport' }}
                &nbsp;·&nbsp;
                {{ $event->approvedParticipants->count() }} participants
                &nbsp;·&nbsp;
                Host: {{ $event->host->name }}
            </p>
        </div>

        <span class="font-display text-[0.6rem] tracking-widest text-white/20 uppercase shrink-0">
            {{ $messages->count() }} msgs
        </span>
    </div>

    {{-- Messages --}}
    <div class="flex-1 overflow-y-auto py-5 space-y-3" id="chat-messages">
        @forelse($messages as $message)
            @php
                $isMine   = $message->user_id === auth()->id();
                $isSystem = $message->type === 'system';
            @endphp

            @if($isSystem)
                {{-- System message — centered --}}
                <div class="flex justify-center">
                    <span class="font-display text-[0.58rem] tracking-widest text-white/20 uppercase px-3 py-1"
                          style="border:1px solid rgb(var(--fg) / 0.06);background:rgb(var(--fg) / 0.02)">
                        {{ $message->content }}
                    </span>
                </div>
            @else
                <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }} items-end gap-2">

                    {{-- Other's avatar --}}
                    @if(!$isMine)
                        @if($message->user->avatar_url)
                            <img src="{{ Storage::url($message->user->avatar_url) }}"
                                 class="h-7 w-7 object-cover shrink-0 self-end"
                                 style="border:1px solid rgb(var(--border-base))">
                        @else
                            <div class="h-7 w-7 flex items-center justify-center shrink-0 self-end"
                                 style="background:rgb(var(--fg) / 0.05);border:1px solid rgb(var(--fg) / 0.08)">
                                <span class="font-display text-white/40 text-xs">
                                    {{ strtoupper(substr($message->user->name, 0, 1)) }}
                                </span>
                            </div>
                        @endif
                    @endif

                    <div class="max-w-xs lg:max-w-md space-y-1">
                        {{-- Sender name (others only) --}}
                        @if(!$isMine)
                            <p class="font-display text-[0.58rem] tracking-widest text-white/35 uppercase px-1">
                                {{ $message->user->name }}
                            </p>
                        @endif

                        {{-- Bubble --}}
                        <div style="{{ $isMine
                                ? 'background:rgba(249,115,22,0.13);border:1px solid rgba(249,115,22,0.28);'
                                : 'background:rgb(var(--fg) / 0.04);border:1px solid rgb(var(--fg) / 0.08);'
                             }} padding:.65rem 1rem;">

                            @if($message->type === 'image' && $message->file_url)
                                <img src="{{ Storage::url($message->file_url) }}"
                                     alt="image" class="max-w-full mb-2"
                                     style="max-height:220px;object-fit:cover">
                            @endif

                            @if($message->type === 'file' && $message->file_url)
                                <a href="{{ Storage::url($message->file_url) }}" target="_blank"
                                   class="flex items-center gap-2 mb-1 transition"
                                   style="color:rgb(var(--fg) / 0.55);font-size:.78rem"
                                   onmouseover="this.style.color='#F97316'"
                                   onmouseout="this.style.color='rgb(var(--fg) / 0.55)'">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                    <span>{{ $message->file_name ?? 'Attachment' }}</span>
                                </a>
                            @endif

                            @if($message->content)
                                <p class="text-sm leading-relaxed"
                                   style="color:{{ $isMine ? 'rgb(var(--fg) / 0.9)' : 'rgb(var(--fg) / 0.75)' }}">
                                    {{ $message->content }}
                                </p>
                            @endif
                        </div>

                        {{-- Timestamp + delete --}}
                        <div class="flex items-center gap-3 px-1 {{ $isMine ? 'justify-end' : '' }}">
                            <span class="font-display text-[0.55rem] tracking-widest"
                                  style="color:rgb(var(--fg) / 0.2)">
                                {{ $message->created_at->format('H:i') }}
                            </span>
                            @if($isMine)
                                <form action="{{ route('chat.destroy', [$event, $message]) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="font-display text-[0.55rem] tracking-widest uppercase transition"
                                            style="color:rgb(var(--fg) / 0.15)"
                                            onmouseover="this.style.color='#ef4444'"
                                            onmouseout="this.style.color='rgb(var(--fg) / 0.15)'">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

        @empty
            <div class="flex flex-col items-center justify-center h-full" style="padding:4rem 0">
                <div class="h-16 w-16 flex items-center justify-center mb-4"
                     style="border:1px solid rgba(249,115,22,0.2);background:rgba(249,115,22,0.04)">
                    <svg class="w-7 h-7 text-accent/40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                    </svg>
                </div>
                <p class="font-display text-sm uppercase tracking-widest text-white/30">No messages yet</p>
                <p class="text-white/20 text-xs mt-2">Be the first to say something</p>
            </div>
        @endforelse
    </div>

    {{-- Input bar --}}
    <div style="border-top:1px solid rgb(var(--border-base));padding-top:1rem">
        <form action="{{ route('chat.send', $event) }}" method="POST"
              enctype="multipart/form-data"
              class="flex items-center gap-2">
            @csrf

            {{-- Image attach --}}
            <label class="flex items-center justify-center shrink-0 cursor-pointer"
                   style="width:44px;height:44px;border:1px solid rgb(var(--fg) / 0.1);background:rgb(var(--fg) / 0.03);transition:border-color .2s"
                   title="Attach image"
                   onmouseover="this.style.borderColor='rgba(249,115,22,0.4)'"
                   onmouseout="this.style.borderColor='rgb(var(--fg) / 0.1)'">
                <svg class="w-4 h-4" style="color:rgb(var(--fg) / 0.3)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                </svg>
                <input type="file" name="file" accept="image/*" class="hidden" onchange="this.form.submit()">
            </label>

            {{-- File attach --}}
            <label class="flex items-center justify-center shrink-0 cursor-pointer"
                   style="width:44px;height:44px;border:1px solid rgb(var(--fg) / 0.1);background:rgb(var(--fg) / 0.03);transition:border-color .2s"
                   title="Attach file"
                   onmouseover="this.style.borderColor='rgba(249,115,22,0.4)'"
                   onmouseout="this.style.borderColor='rgb(var(--fg) / 0.1)'">
                <svg class="w-4 h-4" style="color:rgb(var(--fg) / 0.3)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                </svg>
                <input type="file" name="file" accept=".pdf,.doc,.docx" class="hidden" onchange="this.form.submit()">
            </label>

            {{-- Text input --}}
            <input type="text" name="content" placeholder="Type a message..."
                   autocomplete="off"
                   style="flex:1;background:rgb(var(--fg) / 0.04);border:1px solid rgb(var(--fg) / 0.10);
                          color:rgb(var(--fg));padding:.7rem 1rem;font-size:.88rem;outline:none;
                          font-family:'Inter',sans-serif;transition:border-color .2s"
                   onfocus="this.style.borderColor='#F97316'"
                   onblur="this.style.borderColor='rgb(var(--fg) / 0.10)'"
            />

            {{-- Send --}}
            <button type="submit"
                    style="padding:.7rem 1.4rem;background:#D62B2B;border:none;cursor:pointer;
                           font-family:'Oswald',sans-serif;font-size:.72rem;font-weight:600;
                           letter-spacing:.18em;text-transform:uppercase;color:#fff;
                           white-space:nowrap;flex-shrink:0;transition:background .2s"
                    onmouseover="this.style.background='#F97316'"
                    onmouseout="this.style.background='#D62B2B'">
                Send
            </button>
        </form>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('chat-messages');
    if (el) el.scrollTop = el.scrollHeight;
});
</script>
@endsection
