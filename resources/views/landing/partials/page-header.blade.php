<section class="hero-grid relative overflow-hidden bg-teal-50/50 pt-24 pb-8 sm:pt-28 sm:pb-10">

    <div class="absolute -right-24 -top-24 h-64 w-64 rounded-full bg-teal-100/40 blur-3xl"></div>
    <div class="absolute -bottom-28 -left-24 h-64 w-64 rounded-full bg-teal-100/30 blur-3xl"></div>

    <div class="relative mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">

        {{-- Breadcrumb --}}
        <div class="reveal mb-3 flex items-center justify-center gap-1.5 text-[11px] font-medium text-slate-400">
            <a href="{{ route('home') }}" class="transition hover:text-teal-700">
                হোম
            </a>

            <i data-lucide="chevron-right" class="h-3 w-3"></i>

            <span class="text-teal-700">
                {{ $title }}
            </span>
        </div>

        {{-- Eyebrow --}}
        <span
            class="reveal inline-flex items-center gap-1.5 rounded-full border border-teal-100 bg-teal-50 px-3 py-1 text-[11px] font-semibold text-teal-700">
            <span class="h-1.5 w-1.5 rounded-full bg-teal-600"></span>
            {{ $eyebrow }}
        </span>

        {{-- Title --}}
        <h1
            class="reveal mt-1 text-[1.9rem] font-bold leading-[1.2] tracking-tight text-slate-900 sm:text-[2.4rem]">
            {{ $title }}
        </h1>

        {{-- Description --}}
        @isset($description)
        <p class="reveal mx-auto mt-2 max-w-2xl text-[13px] leading-6 text-slate-500 sm:text-[14px]">
            {{ $description }}
        </p>
        @endisset

    </div>
</section>