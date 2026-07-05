@extends('layouts.app')

@section('title', 'Events')

@section('content')
<div class="space-y-12 pm-anim-load">

    {{-- Page Header --}}
    <header class="border-b border-white/10 pb-10">
        <p class="pm-section-title" data-animate="fade-down">The Calendar</p>
        <h1 class="font-display text-5xl uppercase mt-4 leading-tight" data-animate="fade-up">
            Upcoming <span class="text-accent">Events</span>
        </h1>
        <p class="text-white/50 mt-4 max-w-xl leading-relaxed" data-animate="fade-up">
            Browse open sessions, weekend leagues, and competitive fixtures.
        </p>
    </header>

    {{-- Filters + CTA --}}
    <section class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between" data-animate="fade-up">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <select name="sport_id" onchange="this.form.submit()" class="pm-input !py-2.5 !text-xs w-auto">
                <option value="">All Sports</option>
                @foreach($sports as $sport)
                    <option value="{{ $sport->id }}" {{ request('sport_id') == $sport->id ? 'selected' : '' }}>
                        {{ $sport->name }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="date" value="{{ request('date') }}"
                class="pm-input !py-2.5 !text-xs w-auto [color-scheme:dark]" />
            @if(request('sport_id') || request('date'))
                <a href="{{ route('events.index') }}"
                   class="font-display text-[0.65rem] tracking-widest text-white/40 hover:text-accent uppercase transition">
                    Reset
                </a>
            @endif
        </form>
        <a href="{{ route('events.create') }}" class="pm-btn-primary self-start">
            Host an Event &rarr;
        </a>
    </section>

    {{-- Events Grid --}}
    @if($events->isEmpty())
        <section class="border border-dashed border-white/15 bg-ink-900/30 p-20 text-center" data-animate="zoom-in">
            <p class="pm-section-title">No Events</p>
            <p class="font-display text-2xl uppercase text-white mt-3">Nothing scheduled yet</p>
            <a href="{{ route('events.create') }}" class="pm-btn-primary inline-flex mt-6 text-[0.7rem] py-3 px-5">
                Host the First Event &rarr;
            </a>
        </section>
    @else
        <section class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach($events as $event)
                <div data-animate="fade-up">
                    @include('events.partials.event-card', ['event' => $event])
                </div>
            @endforeach
        </section>

        <div class="flex justify-center pt-4" data-animate="fade-up">
            {{ $events->appends(request()->all())->links() }}
        </div>
    @endif

</div>
@endsection
