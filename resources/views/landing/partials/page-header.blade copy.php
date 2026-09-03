<section class="hero-grid relative overflow-hidden bg-white pt-32 pb-16 sm:pt-36 sm:pb-20">
    <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-teal-100/50 blur-3xl"></div>
    <div class="absolute -bottom-32 -left-24 h-72 w-72 rounded-full bg-amber-100/40 blur-3xl"></div>

    <div class="relative mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
        <div class="reveal mb-4 flex items-center justify-center gap-2 text-[12px] font-medium text-slate-500">
            <a href="{{ route('home') }}" class="transition hover:text-teal-700">হোম</a>
            <i data-lucide="chevron-right" class="h-3.5 w-3.5"></i>
            <span class="text-teal-700">{{ $title }}</span>
        </div>

        <span
            class="reveal inline-flex items-center gap-2 rounded-full border border-teal-100 bg-teal-50 px-3 py-1.5 text-[13px] font-medium text-teal-700">
            <span class="h-1.5 w-1.5 rounded-full bg-teal-600"></span>
            {{ $eyebrow }}
        </span>

        <h1 class="reveal mt-5 text-[2rem] font-bold leading-[1.25] tracking-tight text-slate-900 sm:text-[2.6rem]">
            {{ $title }}
        </h1>

        @isset($description)
        <p class="reveal mx-auto mt-4 max-w-2xl text-[14px] leading-7 text-slate-600 sm:text-[15px]">
            {{ $description }}
        </p>
        @endisset
    </div>
</section>
