@extends('layouts.app')

@section('title', 'Chat — ' . $connection->getOtherUser(Auth::user())->name)

@section('content')
@php $other = $connection->getOtherUser(Auth::user()); @endphp

<div class="flex flex-col" style="height:calc(100vh - 140px);max-width:760px;margin:0 auto">

    {{-- Header --}}
    <div class="flex items-center gap-4 pb-5 mb-1" style="border-bottom:1px solid rgb(var(--border-base))">
        <a href="{{ route('connections.index') }}"
           class="font-display text-[0.65rem] tracking-widest text-white/30 hover:text-accent uppercase transition shrink-0">
            ← Back
        </a>

        {{-- Avatar --}}
        @if($other->avatar_url)
            <img src="{{ Storage::url($other->avatar_url) }}"
                 alt="{{ $other->name }}"
                 class="h-10 w-10 object-cover shrink-0" style="border:1px solid rgb(var(--border-base))">
        @else
            <div class="h-10 w-10 flex items-center justify-center shrink-0 bg-accent/10"
                 style="border:1px solid rgba(249,115,22,.3)">
                <span class="font-display text-accent text-base">{{ strtoupper(substr($other->name, 0, 1)) }}</span>
            </div>
        @endif

        <div class="flex-1 min-w-0">
            <h2 class="font-display text-xl uppercase text-white truncate" style="font-weight:600">{{ $other->name }}</h2>
            <p class="font-display text-[0.58rem] tracking-widest text-white/30 uppercase mt-0.5">
                Connected {{ $connection->created_at->diffForHumans() }}
            </p>
        </div>

        <a href="{{ route('player.profile', $other) }}"
           class="font-display text-[0.6rem] tracking-widest text-white/25 hover:text-accent uppercase transition shrink-0">
            View Profile →
        </a>
    </div>

    {{-- Messages --}}
    <div class="flex-1 overflow-y-auto py-4 space-y-3" id="messages">
        @forelse($connection->messages as $msg)
            @php $isMine = $msg->sender_id === Auth::id(); @endphp

            <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }} items-end gap-2">

                {{-- Other's avatar --}}
                @if(!$isMine)
                    @if($other->avatar_url)
                        <img src="{{ Storage::url($other->avatar_url) }}"
                             class="h-7 w-7 object-cover shrink-0 self-end"
                             style="border:1px solid rgb(var(--border-base))">
                    @else
                        <div class="h-7 w-7 flex items-center justify-center shrink-0 self-end bg-white/5"
                             style="border:1px solid rgb(var(--fg) / .1)">
                            <span class="font-display text-white/50 text-xs">{{ strtoupper(substr($other->name,0,1)) }}</span>
                        </div>
                    @endif
                @endif

                <div class="max-w-[75%] lg:max-w-md space-y-1">
                    {{-- Bubble --}}
                    <div class="{{ $isMine ? 'ml-auto' : '' }}"
                         style="{{ $isMine
                             ? 'background:rgba(249,115,22,0.15);border:1px solid rgba(249,115,22,0.3);'
                             : 'background:rgb(var(--fg) / 0.04);border:1px solid rgb(var(--fg) / 0.08);'
                         }} padding:.65rem 1rem;">

                        @if($msg->type === 'image' && $msg->file_url)
                            <img src="{{ Storage::url($msg->file_url) }}"
                                 alt="image" class="max-w-full mb-2"
                                 style="max-height:220px;object-fit:cover">
                        @endif

                        @if($msg->type === 'file' && $msg->file_url)
                            <a href="{{ Storage::url($msg->file_url) }}" target="_blank"
                               class="flex items-center gap-2 mb-1 hover:text-accent transition"
                               style="color:rgb(var(--fg) / 0.6);font-size:.78rem">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                <span>{{ $msg->file_name ?? 'Attachment' }}</span>
                            </a>
                        @endif

                        @if($msg->message)
                            <p class="text-sm" style="color:{{ $isMine ? 'rgb(var(--fg) / 0.9)' : 'rgb(var(--fg) / 0.75)' }}">
                                {{ $msg->message }}
                            </p>
                        @endif
                    </div>

                    {{-- Timestamp --}}
                    <p class="font-display text-[0.55rem] tracking-widest {{ $isMine ? 'text-right' : '' }}"
                       style="color:rgb(var(--fg) / 0.2)">
                        {{ $msg->created_at->format('H:i') }}
                    </p>
                </div>
            </div>

        @empty
            <div class="flex flex-col items-center justify-center h-full" style="padding:4rem 0">
                <div class="h-16 w-16 flex items-center justify-center mb-4"
                     style="border:1px solid rgba(249,115,22,0.2);background:rgba(249,115,22,0.04)">
                    <svg class="w-7 h-7 text-accent/40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <p class="font-display text-sm uppercase tracking-widest text-white/30">No messages yet</p>
                <p class="text-white/20 text-xs mt-2">Say hi to {{ $other->name }}</p>
            </div>
        @endforelse
    </div>

    {{-- Input bar --}}
    <div style="border-top:1px solid rgb(var(--border-base));padding-top:1rem;margin-top:.5rem">
        <form method="POST" action="{{ route('connections.sendMessage', $connection) }}"
              enctype="multipart/form-data"
              class="flex items-center gap-2">
            @csrf

            {{-- Attach file --}}
            <label class="flex items-center justify-center shrink-0 cursor-pointer transition"
                   style="width:44px;height:44px;border:1px solid rgb(var(--fg) / 0.1);background:rgb(var(--fg) / 0.03)"
                   title="Attach file">
                <svg class="w-4 h-4" style="color:rgb(var(--fg) / 0.35)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                </svg>
                <input type="file" name="file" class="hidden" accept="image/*,.pdf,.doc,.docx">
            </label>

            {{-- Text input --}}
            <input type="text" name="message" placeholder="Type a message..."
                   autocomplete="off"
                   style="flex:1;background:rgb(var(--fg) / 0.04);border:1px solid rgb(var(--fg) / 0.10);
                          color:rgb(var(--fg));padding:.7rem 1rem;font-size:.88rem;outline:none;
                          font-family:'Inter',sans-serif;transition:border-color .2s"
                   onfocus="this.style.borderColor='#F97316'"
                   onblur="this.style.borderColor='rgb(var(--fg) / 0.10)'"
            />

            {{-- Send --}}
            <button type="submit"
                    style="width:44px;height:44px;background:#D62B2B;border:none;cursor:pointer;
                           display:flex;align-items:center;justify-content:center;shrink-flex:0;
                           transition:background .2s;flex-shrink:0"
                    onmouseover="this.style.background='#F97316'"
                    onmouseout="this.style.background='#D62B2B'">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </form>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('messages');
    if (el) el.scrollTop = el.scrollHeight;
});
</script>
@endsection
