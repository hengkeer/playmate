@extends('layouts.app')

@section('title', 'Field Availability')

@section('content')
<div class="space-y-10">
    <section class="rounded-[2rem] border border-slate-200/80 bg-white/95 p-8 shadow-[0_30px_90px_rgba(15,23,42,0.08)]">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-slate-500">Fields</p>
                <h1 class="text-3xl font-semibold text-slate-900">Available courts and venues</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Browse all field availability at a glance and book the best sessions faster with a clean, enterprise-grade schedule view.</p>
            </div>
            <div class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-5 py-3 text-sm font-semibold text-sky-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18"/><path d="M3 12h18"/><path d="M3 17h18"/></svg>
                Live availability
            </div>
        </div>
    </section>

    @if($fields->isEmpty())
        <section class="rounded-[2rem] border border-slate-200 bg-slate-50 p-10 text-center shadow-sm">
            <p class="text-lg font-semibold text-slate-900">No fields available right now</p>
            <p class="mt-3 text-sm text-slate-600">Check back later or host a game to add more availability in your area.</p>
        </section>
    @else
        <section class="grid gap-8">
            @foreach($fields as $field)
                <article class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-slate-50 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-slate-500">{{ $field->location }}</p>
                            <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ $field->name }}</h2>
                        </div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-[0_10px_30px_rgba(15,23,42,0.05)]">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-sky-500/10 text-sky-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            </span>
                            {{ $field->timeSlots->count() }} slots
                        </div>
                    </div>

                    <div class="grid gap-4 p-6 sm:grid-cols-2 xl:grid-cols-3">
                        @forelse($field->timeSlots as $slot)
                            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $slot->start_time->format('H:i') }} — {{ $slot->end_time->format('H:i') }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $slot->is_available ? 'Open to book' : 'Currently booked' }}</p>
                                    </div>
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $slot->is_available ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $slot->is_available ? 'Available' : 'Booked' }}
                                    </span>
                                </div>
                                @if($slot->is_available)
                                    <p class="mt-4 text-sm text-slate-500">Perfect for last-minute scheduling or a warm-up match.</p>
                                @else
                                    <p class="mt-4 text-sm text-slate-500">This time slot is already reserved by another host.</p>
                                @endif
                            </div>
                        @empty
                            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">
                                No time slots currently listed for this field.
                            </div>
                        @endforelse
                    </div>
                </article>
            @endforeach
        </section>
    @endif
</div>
@endsection