@extends('layouts.app')

@section('title', 'Review ' . $targetUser->name)

@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-white border border-slate-200 rounded-3xl p-8 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900 mb-1">Rate this Player</h1>
        <p class="text-slate-500 text-sm mb-6">Share your experience playing with {{ $targetUser->name }}.</p>

        {{-- Target user info --}}
        <div class="flex items-center gap-4 mb-6 p-4 bg-slate-50 rounded-2xl">
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-sky-500 to-cyan-500 flex items-center justify-center text-white font-bold text-lg shrink-0">
                {{ $targetUser->avatarInitial() }}
            </div>
            <div>
                <h2 class="font-semibold text-slate-900">{{ $targetUser->name }}</h2>
                @if($targetUser->userSports->isNotEmpty())
                    <p class="text-xs text-slate-500 mt-0.5">
                        @foreach($targetUser->userSports->take(3) as $us)
                            <span class="mr-1">{{ $us->sport->name }}</span>
                        @endforeach
                    </p>
                @endif
            </div>
        </div>

        {{-- Review form --}}
        <form method="POST" action="{{ route('reviews.store') }}" class="space-y-5">
            @csrf

            {{-- Star Rating --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Your Rating</label>
                <div class="flex gap-2" id="starRating">
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button" data-val="{{ $i }}"
                            class="text-4xl text-slate-300 hover:text-amber-400 transition cursor-pointer leading-none"
                            onclick="setRating({{ $i }})">★</button>
                    @endfor
                </div>
                <input type="hidden" name="rating" id="ratingInput" value="{{ old('rating', 0) }}">
                <p class="mt-1 text-xs text-slate-400" id="ratingLabel">
                    {{ old('rating') ? ['Terrible', 'Poor', 'Okay', 'Good', 'Excellent'][(old('rating') - 1)] : 'Tap a star to rate' }}
                </p>
                @error('rating') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Comment --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Comment <span class="font-normal text-slate-400">(optional)</span></label>
                <textarea name="comment" rows="3" maxlength="500"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent resize-none"
                    placeholder="Great player! Very friendly and skilled...">{{ old('comment') }}</textarea>
                @error('comment') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Hidden fields --}}
            <input type="hidden" name="reviewed_user_id" value="{{ $targetUser->id }}">
            <input type="hidden" name="source_type" value="{{ $sourceType }}">
            @if($sourceId)
                <input type="hidden" name="source_id" value="{{ $sourceId }}">
            @endif

            {{-- Submit --}}
            <button type="submit" class="w-full rounded-xl bg-sky-600 py-3 text-sm font-bold text-white shadow-lg shadow-sky-200 hover:bg-sky-700 transition">
                Submit Review
            </button>
        </form>
    </div>
</div>

<script>
const labels = ['Terrible', 'Poor', 'Okay', 'Good', 'Excellent'];
function setRating(val) {
    document.getElementById('ratingInput').value = val;
    document.getElementById('ratingLabel').textContent = labels[val - 1];
    document.querySelectorAll('#starRating button').forEach((btn, idx) => {
        btn.classList.toggle('text-amber-400', idx < val);
        btn.classList.toggle('text-slate-300', idx >= val);
    });
}
// Initialize existing rating
const init = parseInt('{{ old('rating', 0) }}');
if (init) setRating(init);
</script>
@endsection