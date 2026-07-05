<script>
function playmateChat(cfg) {
    const STORAGE_KEY = 'playmate_chat_session_id';

    return {
        open: false,
        sending: false,
        error: '',
        input: '',
        sessions: cfg.sessions || [],
        // Restore the last active session across page loads (widget is remounted
        // fresh on every navigation since this app isn't an SPA) so closing and
        // reopening the widget resumes the same conversation instead of starting
        // a new one every time.
        sessionId: (() => {
            const stored = localStorage.getItem(STORAGE_KEY);
            return stored ? parseInt(stored, 10) : (cfg.sessionId || null);
        })(),
        messages: cfg.messages || [],

        init() {},

        async refreshSessions() {
            try {
                const r = await fetch(cfg.indexUrl, { headers: { 'Accept': 'application/json' } });
                if (r.ok) this.sessions = await r.json();
            } catch (e) {}
        },

        async newSession() {
            try {
                const r = await fetch(cfg.storeUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf },
                });
                const s = await r.json();
                this.sessionId = s.id;
                localStorage.setItem(STORAGE_KEY, s.id);
                this.messages = [];
                this.sessions.unshift(s);
                this.$nextTick(() => this.$refs.scroll?.scrollTo({ top: 0 }));
            } catch (e) {
                this.error = 'Failed to create a new session.';
            }
        },

        async loadSession(id) {
            this.sessionId = id;
            localStorage.setItem(STORAGE_KEY, id);
            this.messages = [];
            try {
                const r = await fetch(`${cfg.baseUrl}/${id}/messages`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (r.ok) {
                    this.messages = await r.json();
                    this.$nextTick(() => this.$refs.scroll?.scrollTo({ top: this.$refs.scroll.scrollHeight }));
                } else {
                    // Session no longer accessible (deleted, or belonged to a
                    // previous account on this browser) — drop the stale reference
                    // so the next open starts a fresh session instead of erroring forever.
                    this.sessionId = null;
                    localStorage.removeItem(STORAGE_KEY);
                }
            } catch (e) {
                this.error = 'Failed to load messages.';
            }
        },

        async deleteSession(id) {
            if (!confirm('Delete this chat?')) return;
            try {
                const r = await fetch(`${cfg.baseUrl}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': cfg.csrf }
                });
                if (!r.ok) {
                    this.error = 'Failed to delete session (HTTP ' + r.status + ')';
                    return;
                }
                this.sessions = this.sessions.filter(s => s.id !== id);
                if (this.sessionId === id) {
                    this.sessionId = null;
                    this.messages = [];
                    localStorage.removeItem(STORAGE_KEY);
                }
            } catch (e) {
                this.error = 'Connection failed: ' + e.message;
            }
        },

        async send() {
            const text = this.input.trim();
            if (!text || this.sending || !this.sessionId) return;

            this.error = '';
            this.messages.push({ id: 'tmp-' + Date.now(), role: 'user', content: text });
            this.input = '';
            this.sending = true;
            this.$nextTick(() => this.$refs.scroll?.scrollTo({ top: this.$refs.scroll.scrollHeight }));

            try {
                const r = await fetch(`${cfg.baseUrl}/${this.sessionId}/messages`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': cfg.csrf,
                    },
                body: JSON.stringify({ content: text })
                });

                const data = await r.json();

                if (!r.ok) {
                    this.error = data.error || 'Failed to send message.';
                    this.sending = false;
                    return;
                }

                this.messages.push(data.assistant);
                if (data.session?.title) {
                    const s = this.sessions.find(x => x.id === data.session.id);
                    if (s) s.title = data.session.title;
                }
            } catch (e) {
                this.error = 'Connection failed. Please try again.';
            } finally {
                this.sending = false;
                this.$nextTick(() => this.$refs.scroll?.scrollTo({ top: this.$refs.scroll.scrollHeight }));
            }
        }
    }
}
</script>
