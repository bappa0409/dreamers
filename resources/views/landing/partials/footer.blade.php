@php
$organizationName = $organizationName ?? setting('organization_name', 'Dreamers Association');
$organizationEmail = $organizationEmail ?? setting('organization_email', 'info@dreamersassociation.com');
$organizationPhone = $organizationPhone ?? setting('organization_phone', '+880 1XXX-XXXXXX');
$organizationAddress = $organizationAddress ?? setting('organization_address', 'Dhaka, Bangladesh');
$siteLogo = $siteLogo ?? setting('site_logo');
$nameParts = preg_split('/\s+/', trim($organizationName), 2);
$primaryName = $nameParts[0] ?? $organizationName;
$secondaryName = $nameParts[1] ?? '';
@endphp

<footer class="bg-brand-900 text-white">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">

        <div class="py-12 grid md:grid-cols-2 lg:grid-cols-4 gap-10">

            <div>
                <div class="flex items-center gap-3">
                    @if($siteLogo)
                    <img src="{{ asset('storage/'.$siteLogo) }}" alt="{{ $organizationName }}"
                        class="w-10 h-10 shrink-0 rounded-lg object-contain">
                    @else
                    <div class="w-10 h-10 shrink-0 flex items-center justify-center rounded-lg
                   bg-brand-600 text-white shadow-lg shadow-brand-600/20">
                        <i data-lucide="users-round" class="w-5 h-5"></i>
                    </div>
                    @endif

                    <div class="leading-tight">
                        <div class="text-[22px] font-bold">{{ $primaryName }}</div>
                        @if($secondaryName)
                        <div class="-mt-0.5 text-[12px] font-medium tracking-[0.15em] uppercase">{{ $secondaryName }}</div>
                        @endif
                    </div>
                </div>

                <p class="mt-5 text-sm leading-6 text-brand-200 max-w-xs">
                    সম্মিলিত সঞ্চয়, পরিকল্পিত বিনিয়োগ এবং দায়িত্বশীল
                    ব্যবস্থাপনার মাধ্যমে একটি শক্তিশালী ভবিষ্যৎ গড়ে তোলাই
                    আমাদের লক্ষ্য।
                </p>
            </div>

            <div>
                <h4 class="text-[13px] font-semibold">দ্রুত লিংক</h4>
                <div class="mt-4 space-y-2.5">
                    <a href="{{ route('home') }}"
                        class="block text-sm text-brand-200 hover:text-white transition">হোম</a>
                    <a href="{{ route('about') }}"
                        class="block text-sm text-brand-200 hover:text-white transition">আমাদের
                        সম্পর্কে</a>
                    <a href="{{ route('activities') }}"
                        class="block text-sm text-brand-200 hover:text-white transition">কার্যক্রম</a>
                    <a href="{{ route('transparency') }}"
                        class="block text-sm text-brand-200 hover:text-white transition">স্বচ্ছতা</a>
                    <a href="{{ route('faq') }}"
                        class="block text-sm text-brand-200 hover:text-white transition">প্রশ্নোত্তর</a>
                </div>
            </div>

            <div>
                <h4 class="text-[13px] font-semibold">কার্যক্রম</h4>
                <div class="mt-4 space-y-2.5">
                    <p class="text-sm text-brand-200">জমি ও সম্পদ</p>
                    <p class="text-sm text-brand-200">ব্যবসায়িক বিনিয়োগ</p>
                    <p class="text-sm text-brand-200">পণ্য ক্রয়-বিক্রয়</p>
                    <p class="text-sm text-brand-200">সদস্য কল্যাণ</p>
                </div>
            </div>

            <div>
                <h4 class="text-[13px] font-semibold">যোগাযোগ</h4>
                <div class="mt-4 space-y-3">
                    <div class="flex gap-2 text-sm text-brand-200">
                        <i data-lucide="mail" class="w-4 h-4 shrink-0"></i>
                        {{ $organizationEmail }}
                    </div>
                    <div class="flex gap-2 text-sm text-brand-200">
                        <i data-lucide="phone" class="w-4 h-4 shrink-0"></i>
                        {{ $organizationPhone }}
                    </div>
                    <div class="flex gap-2 text-sm text-brand-200">
                        <i data-lucide="map-pin" class="w-4 h-4 shrink-0"></i>
                        {{ $organizationAddress }}
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-brand-800 py-5 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-[11px] text-brand-300">
                © {{ date('Y') }} {{ $organizationName }}. All rights reserved.
            </p>

            <div class="flex items-center gap-4">
                <a href="#" class="text-brand-300 hover:text-white transition">
                    <i data-lucide="facebook" class="w-4 h-4"></i>
                </a>
                <a href="#" class="text-brand-300 hover:text-white transition">
                    <i data-lucide="instagram" class="w-4 h-4"></i>
                </a>
                <a href="#" class="text-brand-300 hover:text-white transition">
                    <i data-lucide="youtube" class="w-4 h-4"></i>
                </a>
            </div>
        </div>

    </div>
</footer>