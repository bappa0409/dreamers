@extends('layouts.public')

@section('title', 'যোগাযোগ করুন | ' . setting('organization_name', 'Dreamers Association'))
@section('description', setting('organization_name', 'Dreamers Association') . ' এর সাথে যোগাযোগ করুন — ই-মেইল, ফোন বা সরাসরি বার্তা পাঠিয়ে আমাদের সাথে যুক্ত হন।')

@section('content')
@php
$organizationName = setting('organization_name', 'Dreamers Association');
$organizationEmail = setting('organization_email', 'info@dreamersassociation.com');
$organizationPhone = setting('organization_phone', '+880 1XXX-XXXXXX');
$organizationAddress = setting('organization_address', 'Dhaka, Bangladesh');
@endphp

@include('landing.partials.navbar', ['active' => 'contact'])

{{-- PAGE HEADER --}}
@include('landing.partials.page-header', [
'eyebrow' => 'Contact',
'title' => 'যোগাযোগ করুন',
'description' => $organizationName.' সম্পর্কে জানতে বা কোনো প্রশ্ন থাকলে আমাদের সাথে যোগাযোগ করুন। আমরা যত দ্রুত সম্ভব
আপনার বার্তার উত্তর দেওয়ার চেষ্টা করব।',
])

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'হোম', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'যোগাযোগ করুন', 'item' => route('contact')],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

{{-- =========================================================
CONTACT INFO + FORM
========================================================== --}}
<section class="bg-white py-14 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-2">

            <div class="reveal">
                <span class="text-[13px] font-bold uppercase tracking-[0.18em] text-teal-700">Get In Touch</span>
                <h2 class="mt-3 text-[2rem] font-bold text-slate-900 sm:text-[2.5rem]">আমাদের সাথে কথা বলুন</h2>
                <p class="max-w-lg text-[14px] leading-7 text-slate-500">
                    {{ $organizationName }} সম্পর্কে জানতে বা কোনো প্রশ্ন
                    থাকলে নিচের যেকোনো মাধ্যমে অথবা পাশের ফর্মের মাধ্যমে
                    আমাদের সাথে যোগাযোগ করুন।
                </p>

                <div class="mt-8 space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                            <i data-lucide="mail" class="h-4.5 w-4.5"></i>
                        </div>
                        <div>
                            <p class="text-[12px] text-slate-500">Email</p>
                            <p class="text-sm font-semibold text-slate-800">{{ $organizationEmail }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                            <i data-lucide="phone" class="h-4.5 w-4.5"></i>
                        </div>
                        <div>
                            <p class="text-[12px] text-slate-500">Phone</p>
                            <p class="text-sm font-semibold text-slate-800">{{ $organizationPhone }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                            <i data-lucide="map-pin" class="h-4.5 w-4.5"></i>
                        </div>
                        <div>
                            <p class="text-[12px] text-slate-500">Location</p>
                            <p class="text-sm font-semibold text-slate-800">{{ $organizationAddress }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 overflow-hidden rounded-md border border-slate-200 bg-slate-100 shadow-sm">
                    <iframe
                        src="https://www.google.com/maps?q={{ urlencode($organizationAddress) }}&output=embed"
                        class="h-64 w-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        title="{{ $organizationName }} Location"></iframe>
                </div>
            </div>

            <div class="reveal">
                <form id="contactForm" novalidate class="rounded-md border border-slate-200 bg-white p-5 shadow-soft sm:p-6">

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="contactName" class="mb-1.5 block text-[13px] font-semibold text-slate-700">আপনার নাম <span class="text-red-500">*</span></label>
                            <input type="text" id="contactName" name="name" required placeholder="আপনার নাম লিখুন"
                                class="w-full rounded-md border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition placeholder:text-slate-500 focus:border-teal-500 focus:bg-white focus:ring-2 focus:ring-teal-500/10">
                            <p id="contactNameError" class="mt-1 hidden text-[12px] font-medium text-rose-600"></p>
                        </div>

                        <div>
                            <label for="contactEmail" class="mb-1.5 block text-[13px] font-semibold text-slate-700">ই-মেইল <span class="text-red-500">*</span></label>
                            <input type="email" id="contactEmail" name="email" required placeholder="আপনার ই-মেইল"
                                class="w-full rounded-md border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition placeholder:text-slate-500 focus:border-teal-500 focus:bg-white focus:ring-2 focus:ring-teal-500/10">
                            <p id="contactEmailError" class="mt-1 hidden text-[12px] font-medium text-rose-600"></p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="contactSubject" class="mb-1.5 block text-[13px] font-semibold text-slate-700">বিষয় <span class="text-red-500">*</span></label>
                        <input type="text" id="contactSubject" name="subject" required placeholder="কী বিষয়ে যোগাযোগ করতে চান?"
                            class="w-full rounded-md border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition placeholder:text-slate-500 focus:border-teal-500 focus:bg-white focus:ring-2 focus:ring-teal-500/10">
                        <p id="contactSubjectError" class="mt-1 hidden text-[12px] font-medium text-rose-600"></p>
                    </div>

                    <div class="mt-4">
                        <label for="contactMessage" class="mb-1.5 block text-[13px] font-semibold text-slate-700">বার্তা <span class="text-red-500">*</span></label>
                        <textarea id="contactMessage" name="message" rows="5" required placeholder="আপনার বার্তা লিখুন..."
                            class="w-full resize-none rounded-md border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition placeholder:text-slate-500 focus:border-teal-500 focus:bg-white focus:ring-2 focus:ring-teal-500/10"></textarea>
                        <p id="contactMessageError" class="mt-1 hidden text-[12px] font-medium text-rose-600"></p>
                    </div>

                    <button type="submit" id="contactSubmitBtn"
                        class="mt-4 flex w-full cursor-pointer items-center justify-center gap-2 rounded-md bg-teal-700 py-3 text-sm font-semibold text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 disabled:cursor-not-allowed disabled:opacity-70">
                        <span id="contactSubmitBtnText">বার্তা পাঠান</span>
                        <i data-lucide="send" id="contactSubmitBtnIcon" class="h-4 w-4"></i>
                        <i data-lucide="loader-2" id="contactSubmitSpinner" class="hidden h-4 w-4 animate-spin"></i>
                    </button>

                    <p id="formMessage" class="mt-3 hidden text-center text-[13px] font-medium text-emerald-600">
                        আপনার বার্তা সফলভাবে পাঠানো হয়েছে।
                    </p>

                    <p id="formError" class="mt-3 hidden text-center text-[13px] font-medium text-rose-600">
                        দুঃখিত, কিছু একটা সমস্যা হয়েছে। আবার চেষ্টা করুন।
                    </p>

                </form>
            </div>

        </div>
    </div>
</section>

@include('landing.partials.footer')
@endsection

@push('scripts')
@include('landing.partials.scripts-base')
<script>
    /* Contact Form */
    const contactForm = document.getElementById('contactForm');
    const formMessage = document.getElementById('formMessage');
    const formError = document.getElementById('formError');
    const submitBtn = document.getElementById('contactSubmitBtn');
    const submitBtnText = document.getElementById('contactSubmitBtnText');
    const submitBtnIcon = document.getElementById('contactSubmitBtnIcon');
    const submitBtnSpinner = document.getElementById('contactSubmitSpinner');

    const contactFields = [
        {
            input: document.getElementById('contactName'),
            error: document.getElementById('contactNameError'),
            validate: (value) => {
                if (!value) return 'অনুগ্রহ করে আপনার নাম লিখুন।';
                if (value.length > 150) return 'নাম সর্বোচ্চ ১৫০ অক্ষরের মধ্যে দিন।';
                return '';
            },
        },
        {
            input: document.getElementById('contactEmail'),
            error: document.getElementById('contactEmailError'),
            validate: (value) => {
                if (!value) return 'অনুগ্রহ করে আপনার ই-মেইল লিখুন।';
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'সঠিক ই-মেইল ঠিকানা লিখুন।';
                return '';
            },
        },
        {
            input: document.getElementById('contactSubject'),
            error: document.getElementById('contactSubjectError'),
            validate: (value) => {
                if (!value) return 'অনুগ্রহ করে বিষয় লিখুন।';
                if (value.length > 200) return 'বিষয় সর্বোচ্চ ২০০ অক্ষরের মধ্যে দিন।';
                return '';
            },
        },
        {
            input: document.getElementById('contactMessage'),
            error: document.getElementById('contactMessageError'),
            validate: (value) => {
                if (!value) return 'অনুগ্রহ করে আপনার বার্তা লিখুন।';
                if (value.length > 5000) return 'বার্তা সর্বোচ্চ ৫০০০ অক্ষরের মধ্যে দিন।';
                return '';
            },
        },
    ];

    function setFieldError(field, message) {
        if (message) {
            field.input.classList.add('border-rose-400', 'focus:border-rose-500', 'focus:ring-rose-100');
            field.input.classList.remove('border-slate-200', 'focus:border-teal-500', 'focus:ring-teal-500/10');
            field.error.textContent = message;
            field.error.classList.remove('hidden');
        } else {
            field.input.classList.remove('border-rose-400', 'focus:border-rose-500', 'focus:ring-rose-100');
            field.input.classList.add('border-slate-200', 'focus:border-teal-500', 'focus:ring-teal-500/10');
            field.error.textContent = '';
            field.error.classList.add('hidden');
        }
    }

    function validateContactForm() {
        let firstInvalid = null;

        contactFields.forEach((field) => {
            const message = field.validate(field.input.value.trim());
            setFieldError(field, message);

            if (message && !firstInvalid) {
                firstInvalid = field.input;
            }
        });

        if (firstInvalid) {
            firstInvalid.focus();
            return false;
        }

        return true;
    }

    contactFields.forEach((field) => {
        field.input.addEventListener('input', () => {
            if (!field.error.classList.contains('hidden')) {
                setFieldError(field, field.validate(field.input.value.trim()));
            }
        });
    });

    contactForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        formMessage.classList.add('hidden');
        formError.classList.add('hidden');

        if (!validateContactForm()) {
            return;
        }

        submitBtn.disabled = true;
        submitBtnText.textContent = 'পাঠানো হচ্ছে...';
        submitBtnIcon.classList.add('hidden');
        submitBtnSpinner.classList.remove('hidden');

        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');

        const payload = {
            name: contactForm.name.value,
            email: contactForm.email.value,
            subject: contactForm.subject.value,
            message: contactForm.message.value,
        };

        try {
            const response = await fetch('/api/contact', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                if (data.errors) {
                    const fieldMap = { name: 0, email: 1, subject: 2, message: 3 };

                    Object.entries(data.errors).forEach(([key, messages]) => {
                        const field = contactFields[fieldMap[key]];
                        if (field) setFieldError(field, messages[0]);
                    });
                }

                formError.textContent = data.message || 'দুঃখিত, কিছু একটা সমস্যা হয়েছে। আবার চেষ্টা করুন।';
                formError.classList.remove('hidden');
            } else {
                formMessage.classList.remove('hidden');
                contactForm.reset();
                contactFields.forEach((field) => setFieldError(field, ''));

                setTimeout(() => {
                    formMessage.classList.add('hidden');
                }, 4000);
            }
        } catch (error) {
            formError.textContent = 'নেটওয়ার্ক সমস্যা হয়েছে। আবার চেষ্টা করুন।';
            formError.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitBtnText.textContent = 'বার্তা পাঠান';
            submitBtnIcon.classList.remove('hidden');
            submitBtnSpinner.classList.add('hidden');
        }
    });
</script>
@endpush
