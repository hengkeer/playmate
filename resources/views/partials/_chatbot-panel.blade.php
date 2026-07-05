{{-- Floating chatbot widget markup. Requires: $sidebarSessions, $activeSessionId, $activeMessages set in caller. --}}
<div
    x-data="playmateChat(@js([
        'csrf'   => csrf_token(),
        'sessions'  => $sidebarSessions ?? [],
        'sessionId' => $activeSessionId ?? null,
        'messages'  => ($activeMessages ?? collect())->map(fn($m) => [
            'id' => $m->id, 'role' => $m->role,
            'content' => $m->content, 'created_at' => $m->created_at->toIso8601String(),
        ])->values(),
        'indexUrl'    => route('chat.sessions.index'),
        'storeUrl'    => route('chat.sessions.store'),
        'baseUrl'     => url('/chat/sessions'),
    ]))"
    x-init="init()"
    class="fixed bottom-4 right-4 z-40 font-sans"
>
    <button
        x-show="!open"
        @click="open = true; if(!sessionId) { newSession() } else if(messages.length === 0) { loadSession(sessionId) }"
        class="bg-accent hover:bg-orange-400 text-white rounded-full w-14 h-14 shadow-2xl flex items-center justify-center transition"
        title="Open PlayMate Assistant"
    >
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
    </button>

    <div
        x-show="open"
        x-cloak
        @keydown.escape.window="open = false"
        class="bg-ink-900 border border-white/10 shadow-2xl flex flex-col overflow-hidden rounded-2xl w-[min(92vw,28rem)] h-[min(80vh,36rem)]"
    >
        <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <h3 class="font-display text-xs tracking-widest text-white">PLAYMATE ASSISTANT</h3>
            </div>
            <div class="flex items-center gap-3">
                <button @click="newSession()" class="text-xs text-white/60 hover:text-accent">+ NEW</button>
                <button @click="open = false" class="text-white/60 hover:text-accent text-lg leading-none">×</button>
            </div>
        </div>

        <div class="flex-1 flex overflow-hidden">
            <aside class="border-r border-white/10 overflow-y-auto bg-ink-950/50 w-32">
                <div class="p-2 space-y-1">
                    <template x-for="s in sessions" :key="s.id">
                        <div
                            @click="loadSession(s.id)"
                            :class="sessionId === s.id ? 'bg-accent/20 border-accent' : 'border-transparent hover:border-white/20'"
                            class="cursor-pointer border rounded p-2 text-xs text-white/80 truncate"
                            :title="s.title || 'Untitled'"
                        >
                            <span x-text="s.title || 'Untitled'"></span>
                            <button @click.stop="deleteSession(s.id)" class="ml-1 text-white/30 hover:text-red-400 float-right">×</button>
                        </div>
                    </template>
                    <div x-show="sessions.length === 0" class="text-[0.65rem] text-white/40 p-2 text-center">No conversations yet</div>
                </div>
            </aside>

            <main class="flex-1 flex flex-col">
                <div x-ref="scroll" class="flex-1 overflow-y-auto p-3 space-y-2">
                    <template x-for="m in messages" :key="m.id">
                        <div :class="m.role === 'user' ? 'text-right' : 'text-left'">
                            <div
                                :class="m.role === 'user' ? 'bg-accent text-white' : 'bg-white/5 text-white/90 border border-white/10'"
                                class="inline-block max-w-[85%] rounded-2xl px-3 py-2 text-sm whitespace-pre-wrap text-left"
                                x-text="m.content"
                            ></div>
                        </div>
                    </template>
                    <div x-show="sending" class="text-left">
                        <div class="inline-block bg-white/5 border border-white/10 rounded-2xl px-3 py-2 text-sm text-white/60">
                            <span class="animate-pulse">typing...</span>
                        </div>
                    </div>
                    <div x-show="!sending && messages.length === 0" class="text-center text-white/40 text-xs py-8">
                        Ask anything about PlayMate.<br>Try: "how does matchmaking scoring work?"
                    </div>
                </div>

                <form @submit.prevent="send()" class="border-t border-white/10 p-2 flex gap-2">
                    <input
                        x-model="input"
                        :disabled="sending || !sessionId"
                        maxlength="2000"
                        placeholder="Type your question..."
                        class="pm-input flex-1 text-sm rounded-lg px-3 py-2"
                    />
                    <button
                        type="submit"
                        :disabled="sending || !input.trim() || !sessionId"
                        class="pm-btn-primary text-xs px-4 disabled:opacity-40"
                    >SEND</button>
                </form>
                <div x-show="error" class="bg-red-500/10 border-t border-red-500/30 px-3 py-2 text-xs text-red-300" x-text="error"></div>
            </main>
        </div>
    </div>
</div>
