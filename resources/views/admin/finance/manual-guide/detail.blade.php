@extends('layouts.admin')

@section('title','Accounts Guide — Detailed')
@section('page_title','Accounts Guide — Detailed')

@section('content')
<div class="space-y-4">

    {{-- Header --}}
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-journal-text"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">হিসাব ব্যবস্থাপনা (Accounts &amp; Finance) — বিস্তারিত গাইড</h1>
                <p class=" text-xs 2xl:text-sm text-slate-500">প্রতিটি মডিউলে টাকা কীভাবে ডেবিট-ক্রেডিট আকারে জার্নালে পোস্ট হয় — বাস্তব উদাহরণসহ ধাপে ধাপে ব্যাখ্যা।</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('admin.finance.manual-guide') }}"
                class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-2  text-xs 2xl:text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
            >
                <i class="bi bi-arrow-left text-[11px]"></i>
                Accounts Guide (সংক্ষিপ্ত)
            </a>
        </div>
    </div>

    {{-- Cover --}}
    <div class="rounded-md bg-gradient-to-br from-indigo-600 to-violet-600 px-6 py-8 text-white shadow-sm">
        <span class="inline-block rounded-full bg-white/15 px-3 py-1 text-[10px] font-bold uppercase tracking-wide">
            Admin Manual — Detailed Edition
        </span>
        <h2 class="mt-3 text-lg font-bold leading-snug sm:text-xl">
            হিসাব ব্যবস্থাপনা (Accounts &amp; Finance) — সম্পূর্ণ ও বিস্তারিত গাইড
        </h2>
        <p class="mt-2 max-w-2xl  text-xs 2xl:text-sm leading-6 text-indigo-100">
            এই ডকুমেন্টে সিস্টেমের সম্পূর্ণ হিসাব (Accounting) কাঠামো, প্রতিটি মডিউলে টাকা কীভাবে ডেবিট-ক্রেডিট আকারে
            জার্নালে পোস্ট হয়, বাস্তব টাকার অঙ্কসহ ধাপে-ধাপে উদাহরণ, T-অ্যাকাউন্ট, টাইমলাইন এবং ফাইন্যান্সিয়াল
            স্টেটমেন্টে এর প্রভাব বিস্তারিতভাবে দেখানো হয়েছে — যাতে একজন নতুন অ্যাডমিন/হিসাবরক্ষকও পুরো ফ্লো নিজে নিজে
            বুঝে নিতে পারেন।
        </p>
        <div class="mt-5 flex flex-wrap gap-x-7 gap-y-2.5 text-[11px] text-indigo-100">
            <div><span class="block text-[10px] font-semibold uppercase tracking-wide text-indigo-200">সিস্টেম</span>Double-Entry Bookkeeping</div>
            <div><span class="block text-[10px] font-semibold uppercase tracking-wide text-indigo-200">ভিত্তি</span>Accrual Basis</div>
            <div><span class="block text-[10px] font-semibold uppercase tracking-wide text-indigo-200">মুদ্রা</span>৳ (BDT) — সেটিংস থেকে পরিবর্তনযোগ্য</div>
            <div><span class="block text-[10px] font-semibold uppercase tracking-wide text-indigo-200">ধরন</span>Association / Cooperative Finance</div>
            <div><span class="block text-[10px] font-semibold uppercase tracking-wide text-indigo-200">সর্বশেষ আপডেট</span>নতুন সেকশন ৫ — Approval Workflow যোগ করা হয়েছে</div>
        </div>
    </div>

    {{-- Table of Contents --}}
    <div class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <h2 class="mb-3 flex items-center gap-1.5 text-sm font-bold text-slate-800">
            <i class="bi bi-list-ul text-indigo-500"></i> সূচিপত্র (Table of Contents)
        </h2>
        <div class="grid gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wide text-slate-400">শুরুর কথা</p>
                <ul class="space-y-1  text-xs 2xl:text-sm">
                    <li><a href="#overview" class="text-slate-600 hover:text-indigo-600">১. ভূমিকা ও মূলনীতি</a></li>
                    <li><a href="#accrual" class="text-slate-600 hover:text-indigo-600">২. Accrual vs Cash Basis</a></li>
                    <li><a href="#coa" class="text-slate-600 hover:text-indigo-600">৩. হিসাবের চার্ট</a></li>
                    <li><a href="#engine" class="text-slate-600 hover:text-indigo-600">৪. জার্নাল ইঞ্জিন</a></li>
                    <li><a href="#approval-workflow" class="text-slate-600 hover:text-indigo-600">৫. Approval Workflow 🆕</a></li>
                </ul>
            </div>
            <div>
                <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wide text-slate-400">টাকার প্রবাহ (মডিউল অনুযায়ী)</p>
                <ul class="space-y-1  text-xs 2xl:text-sm">
                    <li><a href="#subscription" class="text-slate-600 hover:text-indigo-600">৬. সদস্য চাঁদা</a></li>
                    <li><a href="#charges" class="text-slate-600 hover:text-indigo-600">৭. সদস্য চার্জ</a></li>
                    <li><a href="#loans" class="text-slate-600 hover:text-indigo-600">৮. ঋণ</a></li>
                    <li><a href="#investments" class="text-slate-600 hover:text-indigo-600">৯. বিনিয়োগ</a></li>
                    <li><a href="#shares" class="text-slate-600 hover:text-indigo-600">১০. শেয়ার</a></li>
                    <li><a href="#assets" class="text-slate-600 hover:text-indigo-600">১১. সম্পদ ও অবচয়</a></li>
                    <li><a href="#teller" class="text-slate-600 hover:text-indigo-600">১২. টেলার</a></li>
                    <li><a href="#income-expense" class="text-slate-600 hover:text-indigo-600">১৩. আয় ও ব্যয়</a></li>
                    <li><a href="#manual-journal" class="text-slate-600 hover:text-indigo-600">১৪. ম্যানুয়াল জার্নাল</a></li>
                </ul>
            </div>
            <div>
                <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wide text-slate-400">প্রভাব ও রেফারেন্স</p>
                <ul class="space-y-1  text-xs 2xl:text-sm">
                    <li><a href="#statements" class="text-slate-600 hover:text-indigo-600">১৫. ফাইন্যান্সিয়াল স্টেটমেন্ট</a></li>
                    <li><a href="#dashboard-map" class="text-slate-600 hover:text-indigo-600">১৬. ড্যাশবোর্ডের সংখ্যা</a></li>
                    <li><a href="#appendix" class="text-slate-600 hover:text-indigo-600">১৭. অ্যাপেন্ডিক্স</a></li>
                </ul>
            </div>
            <div>
                <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wide text-slate-400">রিপোর্ট ও রসিদ</p>
                <ul class="space-y-1  text-xs 2xl:text-sm">
                    <li><a href="#reports" class="text-slate-600 hover:text-indigo-600">১৮. রিপোর্টসমূহ</a></li>
                    <li><a href="#receipts" class="text-slate-600 hover:text-indigo-600">১৯. রসিদ / প্রিন্ট</a></li>
                </ul>
            </div>
            <div>
                <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wide text-slate-400">অন্যান্য</p>
                <ul class="space-y-1  text-xs 2xl:text-sm">
                    <li><a href="#permissions" class="text-slate-600 hover:text-indigo-600">২০. পারমিশন ও রোল</a></li>
                    <li><a href="#faq" class="text-slate-600 hover:text-indigo-600">২১. সাধারণ সমস্যা ও সমাধান</a></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- 1. OVERVIEW --}}
    {{-- ============================================================ --}}
    <div id="overview" class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[12px] font-bold text-white">১</span>
            <h2 class="text-sm font-bold text-slate-800">ভূমিকা ও মূলনীতি</h2>
        </div>

        <p class=" text-xs 2xl:text-sm leading-6 text-slate-600">
            এই সিস্টেমের হিসাব বিভাগ <b>Double-Entry Accounting</b> নীতিতে চলে — অর্থাৎ প্রতিটি আর্থিক ঘটনার জন্য
            কমপক্ষে দুইটি হিসাব (Account) প্রভাবিত হয়: একটি <span class="font-semibold text-emerald-600">ডেবিট (Debit)</span>
            এবং একটি <span class="font-semibold text-red-600">ক্রেডিট (Credit)</span>। প্রতিটি লেনদেনে মোট ডেবিট =
            মোট ক্রেডিট না হলে সিস্টেম সেটি পোস্ট করতে দেয় না।
        </p>

        <div class="mt-3 rounded-md border border-indigo-200 bg-indigo-50/50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-indigo-700">
            <p class="mb-1 font-bold">মূল ধারণা</p>
            প্রতিটি টাকার লেনদেন (চাঁদা, ঋণ, খরচ, আয়, সম্পদ ক্রয় ইত্যাদি) সিস্টেমে একটি
            <b>জার্নাল/ট্রানজেকশন (Transaction / Journal Voucher)</b> হিসেবে রেকর্ড হয়। প্রতিটি ভাউচারের একটি ইউনিক
            নাম্বার (Transaction No), তারিখ, বিবরণ, এবং একাধিক এন্ট্রি লাইন (Debit/Credit) থাকে। কোনো মডিউল সরাসরি
            ব্যালেন্স পরিবর্তন করে না — সব সময় একটি জার্নালের মাধ্যমেই পরিবর্তন হয়, তাই যেকোনো সংখ্যার উৎস সবসময়
            ট্রেস করা যায়।
        </div>

        <h3 class="mb-2 mt-4  text-xs 2xl:text-sm font-bold text-slate-800">হিসাবের ৫টি মূল শ্রেণি (Account Types)</h3>
        <div class="overflow-x-auto rounded-md border border-slate-200">
            <table class="w-full  text-xs 2xl:text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">শ্রেণি</th>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">কোড রেঞ্জ</th>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">বৃদ্ধি পায়</th>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">উদাহরণ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr><td class="px-3 py-2 font-semibold text-slate-700">Assets (সম্পদ)</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">1000–1999</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">Debit দিয়ে</td><td class="px-3 py-2 text-slate-500">Cash, Bank, Receivable, Fixed Assets</td></tr>
                    <tr><td class="px-3 py-2 font-semibold text-slate-700">Liabilities (দায়)</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">2000–2999</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">Credit দিয়ে</td><td class="px-3 py-2 text-slate-500">Accounts Payable</td></tr>
                    <tr><td class="px-3 py-2 font-semibold text-slate-700">Equity (তহবিল)</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">3000–3999</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">Credit দিয়ে</td><td class="px-3 py-2 text-slate-500">Association Fund, Member Share Capital</td></tr>
                    <tr><td class="px-3 py-2 font-semibold text-slate-700">Income (আয়)</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">4000–4999</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">Credit দিয়ে</td><td class="px-3 py-2 text-slate-500">Subscription, Investment, Fine Income</td></tr>
                    <tr><td class="px-3 py-2 font-semibold text-slate-700">Expense (ব্যয়)</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">5000–5999</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">Debit দিয়ে</td><td class="px-3 py-2 text-slate-500">Office, Maintenance, Welfare Expense</td></tr>
                </tbody>
            </table>
        </div>

        <div class="mt-3 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-emerald-700">
            <p class="mb-1 font-bold">মনে রাখার সহজ নিয়ম</p>
            Cash/Bank বাড়লে সবসময় <b>Debit</b>, কমলে <b>Credit</b>। Income হলে <b>Credit</b>, Expense হলে <b>Debit</b>।
            Receivable (কারো কাছে পাওনা) বাড়লে <b>Debit</b>, কমলে (আদায় হলে) <b>Credit</b>।
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- 2. ACCRUAL --}}
    {{-- ============================================================ --}}
    <div id="accrual" class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[12px] font-bold text-white">২</span>
            <h2 class="text-sm font-bold text-slate-800">Accrual vs Cash Basis — এই সিস্টেম কোন নিয়মে চলে</h2>
        </div>

        <p class=" text-xs 2xl:text-sm leading-6 text-slate-600">
            এই সিস্টেম <b>Accrual (উপার্জন-ভিত্তিক)</b> পদ্ধতিতে হিসাব রাখে, <b>Cash (নগদ-ভিত্তিক)</b> পদ্ধতিতে নয়।
            এই দুটোর পার্থক্য বোঝা সবচেয়ে জরুরি — কারণ পুরো Subscription ও Charges ফ্লো এই নিয়মের উপর দাঁড়িয়ে আছে।
        </p>

        <div class="mt-3 overflow-x-auto rounded-md border border-slate-200">
            <table class="w-full  text-xs 2xl:text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">পদ্ধতি</th>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">আয় (Income) কখন ধরা হয়</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">Cash Basis</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">যেদিন হাতে টাকা আসে, সেদিন</td></tr>
                    <tr><td class="px-3 py-2 font-semibold text-slate-700">Accrual Basis (এই সিস্টেম)</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">যেদিন পাওনা তৈরি হয় (due generate হয়), <b>টাকা হাতে আসার আগেই</b></td></tr>
                </tbody>
            </table>
        </div>

        <p class="mt-3  text-xs 2xl:text-sm leading-6 text-slate-600">
            তাই কোনো Due তৈরি হওয়ার সাথে সাথেই "Subscription Income" বেড়ে যায় — যদিও ওই টাকা তখনো
            <b>Accounts Receivable</b> (বকেয়া পাওনা) হিসেবে বসে থাকে, Cash/Bank-এ জমা হয়নি। পরে সদস্য টাকা জমা দিলে
            সেই Receivable কমে গিয়ে Cash/Bank বাড়ে — কিন্তু Income আর দ্বিতীয়বার বাড়ে না, কারণ আয়টা ইতিমধ্যেই ধরা হয়ে গেছে।
        </p>

        <div class="mt-3 rounded-md border border-violet-200 bg-violet-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-violet-800">
            <p class="mb-1 font-bold">বাস্তব উদাহরণ দিয়ে পার্থক্য</p>
            ধরুন জানুয়ারি মাসের চাঁদা ৳1,000 due হলো ১ জানুয়ারি, কিন্তু সদস্য টাকা জমা দিলেন ১৫ ফেব্রুয়ারি। Cash Basis
            হলে জানুয়ারির P&amp;L-এ কোনো আয় দেখাতো না, ফেব্রুয়ারিতে ৳1,000 আয় দেখাতো। কিন্তু এই সিস্টেমে (Accrual)
            জানুয়ারির P&amp;L-এই ৳1,000 আয় হিসেবে যোগ হয়ে যায় — কারণ পাওনা তৈরি হয়ে গেছে, টাকা পরে আসুক বা না আসুক।
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- 3. CHART OF ACCOUNTS --}}
    {{-- ============================================================ --}}
    <div id="coa" class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[12px] font-bold text-white">৩</span>
            <h2 class="text-sm font-bold text-slate-800">হিসাবের চার্ট (Chart of Accounts)</h2>
        </div>

        <p class=" text-xs 2xl:text-sm leading-6 text-slate-600">
            সিস্টেমে পূর্বনির্ধারিত (System) হিসাবগুলো নিচে দেওয়া হলো। প্রয়োজনে
            <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">Finance → Chart of Accounts</code>
            থেকে নতুন সাব-অ্যাকাউন্ট যোগ করা যায়, তবে সিস্টেম-অ্যাকাউন্টগুলো মুছে ফেলা যায় না। এই পুরো গাইডে সব
            উদাহরণে নিচের কোডগুলোই ব্যবহার করা হয়েছে।
        </p>

        <div class="mt-3 grid gap-4 sm:grid-cols-2">
            <div class="space-y-3">
                <div>
                    <h4 class="mb-1.5  text-xs 2xl:text-sm font-bold text-indigo-600">1000 — Assets</h4>
                    <div class="overflow-x-auto rounded-md border border-slate-200">
                        <table class="w-full  text-xs 2xl:text-sm">
                            <tbody class="divide-y divide-slate-100">
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">1100</code></td><td class="px-3 py-1.5 text-slate-600">Cash</td></tr>
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">1200</code></td><td class="px-3 py-1.5 text-slate-600">Bank</td></tr>
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">1300</code></td><td class="px-3 py-1.5 text-slate-600">Accounts Receivable</td></tr>
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">1310</code></td><td class="px-3 py-1.5 text-slate-600">Member Loan Receivable</td></tr>
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">1400</code></td><td class="px-3 py-1.5 text-slate-600">Investments</td></tr>
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">1500</code></td><td class="px-3 py-1.5 text-slate-600">Fixed Assets</td></tr>
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">1510–1550</code></td><td class="px-3 py-1.5 text-slate-600">Furniture, Equipment, Vehicles, Others</td></tr>
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">1590</code></td><td class="px-3 py-1.5 text-slate-600">Accumulated Depreciation (contra-asset)</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div>
                    <h4 class="mb-1.5  text-xs 2xl:text-sm font-bold text-indigo-600">2000 — Liabilities</h4>
                    <div class="overflow-x-auto rounded-md border border-slate-200">
                        <table class="w-full  text-xs 2xl:text-sm">
                            <tbody class="divide-y divide-slate-100">
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">2100</code></td><td class="px-3 py-1.5 text-slate-600">Accounts Payable</td></tr>
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">2200</code></td><td class="px-3 py-1.5 text-slate-600">Other Liabilities</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div>
                    <h4 class="mb-1.5  text-xs 2xl:text-sm font-bold text-indigo-600">3000 — Equity</h4>
                    <div class="overflow-x-auto rounded-md border border-slate-200">
                        <table class="w-full  text-xs 2xl:text-sm">
                            <tbody class="divide-y divide-slate-100">
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">3100</code></td><td class="px-3 py-1.5 text-slate-600">Association Fund</td></tr>
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">3200</code></td><td class="px-3 py-1.5 text-slate-600">Accumulated Fund</td></tr>
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">3300</code></td><td class="px-3 py-1.5 text-slate-600">Member Share Capital</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="space-y-3">
                <div>
                    <h4 class="mb-1.5  text-xs 2xl:text-sm font-bold text-indigo-600">4000 — Income</h4>
                    <div class="overflow-x-auto rounded-md border border-slate-200">
                        <table class="w-full  text-xs 2xl:text-sm">
                            <tbody class="divide-y divide-slate-100">
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">4100</code></td><td class="px-3 py-1.5 text-slate-600">Subscription Income</td></tr>
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">4200</code></td><td class="px-3 py-1.5 text-slate-600">Investment Income</td></tr>
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">4300</code></td><td class="px-3 py-1.5 text-slate-600">Donation Income</td></tr>
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">4400/4410</code></td><td class="px-3 py-1.5 text-slate-600">Fine / Late Fine Income</td></tr>
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">4500</code></td><td class="px-3 py-1.5 text-slate-600">Loan Interest Income</td></tr>
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">4900/4910</code></td><td class="px-3 py-1.5 text-slate-600">Other Income / Gain on Asset Disposal</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div>
                    <h4 class="mb-1.5  text-xs 2xl:text-sm font-bold text-indigo-600">5000 — Expense</h4>
                    <div class="overflow-x-auto rounded-md border border-slate-200">
                        <table class="w-full  text-xs 2xl:text-sm">
                            <tbody class="divide-y divide-slate-100">
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">5100</code></td><td class="px-3 py-1.5 text-slate-600">Office Expense</td></tr>
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">5200</code></td><td class="px-3 py-1.5 text-slate-600">Maintenance Expense</td></tr>
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">5300</code></td><td class="px-3 py-1.5 text-slate-600">Utility Expense</td></tr>
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">5400</code></td><td class="px-3 py-1.5 text-slate-600">Welfare Assistance Expense</td></tr>
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">5800</code></td><td class="px-3 py-1.5 text-slate-600">Depreciation Expense</td></tr>
                                <tr><td class="px-3 py-1.5"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">5900/5910</code></td><td class="px-3 py-1.5 text-slate-600">Other Expense / Loss on Asset Disposal</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 rounded-md border border-indigo-200 bg-indigo-50/50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-indigo-700">
            Asset ও Expense অ্যাকাউন্টের স্বাভাবিক ব্যালেন্স <b>Debit</b>-এ বাড়ে; Liability, Equity ও Income
            অ্যাকাউন্টের স্বাভাবিক ব্যালেন্স <b>Credit</b>-এ বাড়ে — এটাই ডাবল-এন্ট্রির মূল নিয়ম যা এই গাইডের প্রতিটা
            উদাহরণে অনুসরণ করা হয়েছে।
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- 4. ENGINE --}}
    {{-- ============================================================ --}}
    <div id="engine" class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[12px] font-bold text-white">৪</span>
            <h2 class="text-sm font-bold text-slate-800">জার্নাল ইঞ্জিন কীভাবে কাজ করে</h2>
        </div>

        <p class=" text-xs 2xl:text-sm leading-6 text-slate-600">
            সিস্টেমের প্রতিটি মডিউল (চাঁদা, ঋণ, খরচ, সম্পদ ইত্যাদি) নিজে থেকে সরাসরি হিসাবের খাতা পরিবর্তন করে না —
            বরং একটি কেন্দ্রীয় <b>Accounting Engine</b>-কে অনুরোধ পাঠায়, যেটি নিচের নিয়মগুলো মেনে জার্নাল পোস্ট করে:
        </p>

        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-md border border-slate-200 bg-white p-3">
                <span class="mb-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[11px] font-bold text-white">১</span>
                <p class=" text-xs 2xl:text-sm text-slate-600"><b class="text-slate-800">ব্যালেন্স যাচাই</b><br>মোট Debit = মোট Credit না হলে এন্ট্রি বাতিল হয়ে যায়।</p>
            </div>
            <div class="rounded-md border border-slate-200 bg-white p-3">
                <span class="mb-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[11px] font-bold text-white">২</span>
                <p class=" text-xs 2xl:text-sm text-slate-600"><b class="text-slate-800">ইউনিক নাম্বার</b><br>প্রতিটি ভাউচারে অটো-জেনারেটেড Transaction No বসে।</p>
            </div>
            <div class="rounded-md border border-slate-200 bg-white p-3">
                <span class="mb-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[11px] font-bold text-white">৩</span>
                <p class=" text-xs 2xl:text-sm text-slate-600"><b class="text-slate-800">Idempotency</b><br>একই পেমেন্ট/ইভেন্টের জন্য একাধিকবার ভুলে ডাবল-এন্ট্রি হওয়া আটকানো হয়।</p>
            </div>
            <div class="rounded-md border border-slate-200 bg-white p-3">
                <span class="mb-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[11px] font-bold text-white">৪</span>
                <p class=" text-xs 2xl:text-sm text-slate-600"><b class="text-slate-800">তাৎক্ষণিক Posted</b><br>বেশিরভাগ স্বয়ংক্রিয় জার্নাল সরাসরি <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">Posted</span> স্ট্যাটাসে সেভ হয়।</p>
            </div>
        </div>

        <h3 class="mb-2 mt-4  text-xs 2xl:text-sm font-bold text-slate-800">স্ট্যাটাস</h3>
        <div class="overflow-x-auto rounded-md border border-slate-200">
            <table class="w-full  text-xs 2xl:text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">স্ট্যাটাস</th>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">অর্থ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr><td class="px-3 py-2 text-xs 2xl:text-sm"><span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">Posted</span></td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">চূড়ান্তভাবে খাতায় জমা হয়েছে; হিসাবের ব্যালেন্সে প্রভাব ফেলে।</td></tr>
                    <tr><td class="px-3 py-2 text-xs 2xl:text-sm"><span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">Draft</span></td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">এখনো খাতায় প্রভাব ফেলেনি (সাধারণত ব্যবহার হয় না, ম্যানুয়াল জার্নালে সরাসরি Posted হয়)।</td></tr>
                    <tr><td class="px-3 py-2 text-xs 2xl:text-sm"><span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-700">Cancelled / Reversed</span></td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">মূল জার্নাল বাতিল দেখানো হয়েছে; এর বিপরীতে একটি নতুন Reversal জার্নাল পোস্ট হয়েছে।</td></tr>
                </tbody>
            </table>
        </div>

        <div class="mt-3 rounded-md border border-amber-200 bg-amber-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-amber-800">
            <p class="mb-1 font-bold">গুরুত্বপূর্ণ</p>
            ভুল জার্নাল হয়ে গেলে সরাসরি এডিট বা ডিলিট করা যায় না — এটি হিসাবরক্ষণের একটি নিরাপত্তা নিয়ম, যাতে অডিট
            ট্রেইল ঠিক থাকে। ভুল সংশোধনের একমাত্র উপায় হলো <b>Reverse Journal</b> (দেখুন সেকশন ১৪)।
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- 5. APPROVAL WORKFLOW --}}
    {{-- ============================================================ --}}
    <div id="approval-workflow" class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[12px] font-bold text-white">৫</span>
            <h2 class="text-sm font-bold text-slate-800">Approval Workflow (অনুমোদন প্রবাহ)</h2>
            <span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-[10px] font-bold text-amber-700">🆕 নতুন</span>
        </div>

        <p class=" text-xs 2xl:text-sm leading-6 text-slate-600">
            সিস্টেমে নির্দিষ্ট কিছু "sensitive" মডিউল/অ্যাকশনের জন্য একটি কেন্দ্রীয় <b>Approval Workflow Engine</b> আছে,
            যা <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">Settings/Approval → Workflow Builder</code>
            থেকে নিয়ন্ত্রণ করা হয়। এই ইঞ্জিনটাই ঠিক করে দেয় — কোনো এন্ট্রি তৈরি হওয়ার সাথে সাথেই হিসাবের খাতায়
            (Journal/Ledger) পোস্ট হয়ে যাবে, নাকি আগে একজন (বা একাধিক ধাপে সর্বোচ্চ ৩ জন) নির্ধারিত Approver-এর
            অনুমোদনের জন্য অপেক্ষা করবে। প্রতিটি মডিউল/অ্যাকশনের জন্য এই সিদ্ধান্তটা <b>একটাই সুইচ</b> — ওই
            module/action-এর জন্য একটি <b>Active workflow</b> (কমপক্ষে ১টা approver-step সহ) সেভ করা আছে কিনা।
        </p>

        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-emerald-700">
                <p class="mb-1 font-bold">✅ Workflow না থাকলে (বা Inactive/unmarked) — সাথে সাথে Posted</p>
                এন্ট্রি ব্লক হয় না। সিস্টেম নিজে থেকেই তা <b>Auto-Approve (Self-Approved)</b> করে দেয় এবং ঠিক সেই
                একই পোস্টিং লজিক চালায় যা একজন মানুষ শেষ ধাপে Approve করলে চলত। ফলাফল: এন্ট্রিটা মুহূর্তেই
                Posted / Verified / Activated হয়ে যায়, ড্যাশবোর্ড ও রিপোর্টে সাথে সাথে দেখা যায়, কারো Approve
                করার জন্য অপেক্ষা করতে হয় না।
            </div>
            <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-amber-800">
                <p class="mb-1 font-bold">⏳ Workflow Active থাকলে — Pending, Approve না হওয়া পর্যন্ত অপ্রভাবী</p>
                এন্ট্রিটা Pending অবস্থায় তৈরি হয় (Manual Journal-এর ক্ষেত্রে Draft) — হিসাবের খাতায় কোনো প্রভাব পড়ে
                না, ব্যালেন্স/রিপোর্টে যোগ হয় না। নির্ধারিত Approver-কে Approvals মেনুতে নোটিফিকেশন যায়; তিনি Approve
                করলে (একাধিক ধাপ থাকলে ক্রমান্বয়ে সবগুলো ধাপ শেষ হলে) তবেই একই পোস্টিং লজিক চলে এবং জার্নাল Posted হয়।
                কেউ Reject করলে এন্ট্রিটা বাতিল/প্রত্যাখ্যাত থেকে যায়, কখনো পোস্ট হয় না।
            </div>
        </div>

        <h3 class="mb-2 mt-4  text-xs 2xl:text-sm font-bold text-slate-800">কোন মডিউল/অ্যাকশনগুলোর জন্য এই সুইচ কাজ করে</h3>
        <p class="mb-2  text-xs 2xl:text-sm leading-6 text-slate-600">
            Workflow Builder-এ শুধু নিচের নির্দিষ্ট ১৩টি module/action জোড়ার জন্যই workflow বানানো যায় — এর বাইরের
            কোনো মডিউলে এই সুইচ প্রযোজ্য নয়। প্রতিটির পাশে workflow না থাকলে ঠিক কী ঘটে তা দেওয়া হলো:
        </p>
        <div class="overflow-x-auto rounded-md border border-slate-200">
            <table class="w-full  text-xs 2xl:text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">Module</th>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">Action</th>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">Workflow না থাকলে (Auto-Approve)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                    $wfRows = [
                        ['Member','create','নতুন সদস্য সাথে সাথেই Approve হয়ে যায়, initial share auto-purchase হয়ে যায়।'],
                        ['Loan','request','লোন রিকোয়েস্ট সাথে সাথে Approve হয়ে Disbursement পোস্টিং চলে যায়।'],
                        ['Welfare','request','Welfare রিকোয়েস্ট সাথে সাথে Approve হয়ে Assistance পোস্টিং চলে যায়।'],
                        ['MemberExit','request','Exit রিকোয়েস্ট সাথে সাথে Approve হয়ে Settlement পোস্টিং চলে যায়।'],
                        ['MemberShare','request','শেয়ার ক্রয় সাথে সাথে Approve/Verify হয়ে যায়।'],
                        ['Tour','approve','ট্যুর সাথে সাথে Approve হয়ে যায়।'],
                        ['Account','create, update, delete','অ্যাকাউন্ট তৈরি/আপডেট/ডিলিট সাথে সাথেই effective হয়ে যায়।'],
                        ['Income','create','Income এন্ট্রি সাথে সাথেই Posted হয়ে যায়।'],
                        ['Expense','create','Expense এন্ট্রি সাথে সাথেই Posted হয়ে যায়।'],
                        ['SubscriptionPayment','verify','সাবস্ক্রিপশন পেমেন্ট সাথে সাথে Verify হয়ে যায়।'],
                        ['Charge','create','মেম্বার চার্জ সাথে সাথেই Posted হয়ে যায়।'],
                        ['Asset','create','অ্যাসেট ক্রয় সাথে সাথেই Posted হয়ে যায়।'],
                        ['JournalEntry','create','ম্যানুয়াল জার্নাল Draft-এ না থেকে সাথে সাথেই Posted হয়ে যায়।'],
                    ];
                    @endphp
                    @foreach($wfRows as [$module, $action, $behavior])
                    <tr>
                        <td class="px-3 py-2 font-semibold text-slate-700">{{ $module }}</td>
                        <td class="px-3 py-2 text-xs 2xl:text-sm"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">{{ $action }}</code></td>
                        <td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">{{ $behavior }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3 rounded-md border border-indigo-200 bg-indigo-50/50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-indigo-700">
            📌 এই টেবিলটা <code class="rounded bg-white px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">Finance → Accounts Guide</code>
            পেজে (in-app রেফারেন্স) যে টেবিল দেখায়, তার সাথে হুবহু মিল রেখে রাখা হয়েছে — যেকোনো দ্বিধা হলে সরাসরি
            অ্যাপের ওই পেজেও যাচাই করা যাবে।
        </div>

        <h3 class="mb-2 mt-4  text-xs 2xl:text-sm font-bold text-slate-800">Approve / Reject / Cancel হলে কী হয়</h3>
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-md border border-slate-200 bg-white p-3">
                <span class="mb-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[11px] font-bold text-white">১</span>
                <p class=" text-xs 2xl:text-sm text-slate-600"><b class="text-slate-800">Approve</b><br>একাধিক ধাপ (স্টেপ) থাকলে ক্রমান্বয়ে পরের Approver-কে নোটিফাই করা হয়; সর্বশেষ ধাপ Approve হলেই কেবল আসল পোস্টিং (journal/verify/activate) চলে।</p>
            </div>
            <div class="rounded-md border border-slate-200 bg-white p-3">
                <span class="mb-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[11px] font-bold text-white">২</span>
                <p class=" text-xs 2xl:text-sm text-slate-600"><b class="text-slate-800">Reject</b><br>যেকোনো ধাপে Reject হলে সাথে সাথে পুরো রিকোয়েস্ট বাতিল; সংশ্লিষ্ট এন্ট্রি (যেমন পেমেন্ট, চার্জ, অ্যাসেট, জার্নাল) "Rejected" স্ট্যাটাসে থেকে যায়, কোনো পোস্টিং হয় না।</p>
            </div>
            <div class="rounded-md border border-slate-200 bg-white p-3">
                <span class="mb-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[11px] font-bold text-white">৩</span>
                <p class=" text-xs 2xl:text-sm text-slate-600"><b class="text-slate-800">Cancel</b><br>রিকোয়েস্টকারী নিজে অনুমোদনের আগে রিকোয়েস্টটি Cancel করতে পারেন — সংশ্লিষ্ট এন্ট্রি নিষ্ক্রিয়/বাতিল অবস্থায় চলে যায়, ভবিষ্যতে আবার নতুন করে সাবমিট করতে হয়।</p>
            </div>
        </div>

        <div class="mt-3 rounded-md border border-violet-200 bg-violet-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-violet-800">
            <p class="mb-1 font-bold">উদাহরণ</p>
            ধরুন <code class="rounded bg-white px-1.5 py-0.5 font-mono text-[11px] text-violet-700">Expense → create</code>-এর জন্য
            ২-ধাপের workflow বসানো আছে (Treasurer → Secretary)। একজন অ্যাডমিন ৳15,000 অফিস খরচের এন্ট্রি সাবমিট
            করলেন — এটি প্রথমে Pending থাকবে এবং Trial Balance/P&amp;L-এ এখনো দেখাবে না। Treasurer Approve করলে
            সেটা দ্বিতীয় ধাপে (Secretary) যাবে; Secretary-ও Approve করার পরই এন্ট্রিটা জার্নালে Posted হবে এবং
            রিপোর্টে দেখা যাবে। মাঝপথে কেউ Reject করলে এন্ট্রিটা কখনোই পোস্ট হবে না।
        </div>

        <div class="mt-3 rounded-md border border-amber-200 bg-amber-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-amber-800">
            <p class="mb-1 font-bold">সাম্প্রতিক আপডেট (এই গাইডে নতুন সংযোজন)</p>
            এই সেকশনটি নতুন যোগ করা হয়েছে, কারণ Approval Engine-এ কিছু মডিউলের অ্যাকশন-নাম মেলানোর ত্রুটি সংশোধন
            করা হয়েছে (যেমন MemberShare, MemberExit, Loan, Welfare — এগুলোর রিকোয়েস্ট সবসময়
            <code class="rounded bg-white px-1.5 py-0.5 font-mono text-[11px] text-amber-700">action="request"</code>
            দিয়ে তৈরি হয়, কিন্তু আগে Workflow Builder থেকে বানানো কিছু পুরনো workflow ভিন্ন action নামে সেভ হয়ে
            থাকতে পারত, ফলে সেগুলো মিলত না এবং workflow বসানো থাকা সত্ত্বেও এন্ট্রি নীরবে Auto-Approve হয়ে যেত)।
            এখন থেকে আগে সেভ করা workflow-ও সঠিকভাবে কাজ করবে — নতুন করে Workflow Builder-এ গিয়ে সেভ করার দরকার
            নেই। যদি কোনো মডিউলে workflow বসানোর পরও এন্ট্রি সাথে সাথে Posted হয়ে যাচ্ছে বলে মনে হয়, প্রথমে
            <code class="rounded bg-white px-1.5 py-0.5 font-mono text-[11px] text-amber-700">Settings/Approval → Workflow Builder</code>-এ
            গিয়ে সেই module/action-এর workflow সত্যিই <b>Active</b> আছে কিনা এবং কমপক্ষে ১টা approver-step যোগ করা
            আছে কিনা যাচাই করুন।
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- 6. SUBSCRIPTION --}}
    {{-- ============================================================ --}}
    <div id="subscription" class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[12px] font-bold text-white">৬</span>
            <h2 class="text-sm font-bold text-slate-800">সদস্য চাঁদা (Subscription &amp; Dues) — বিস্তারিত ফ্লো</h2>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-md border border-slate-200 bg-white p-3">
                <span class="mb-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[11px] font-bold text-white">১</span>
                <p class=" text-xs 2xl:text-sm text-slate-600">সদস্য Approve হওয়ার সময় / প্রতি মাসের শুরুতে সিস্টেম একটি <b>Due</b> তৈরি করে ও জার্নাল পোস্ট করে।</p>
            </div>
            <div class="rounded-md border border-slate-200 bg-white p-3">
                <span class="mb-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[11px] font-bold text-white">২</span>
                <p class=" text-xs 2xl:text-sm text-slate-600">Due Day পার হয়ে গেলে "Late Fine" সেটিং চালু থাকলে আলাদা জার্নালে ফাইন যোগ হয়।</p>
            </div>
            <div class="rounded-md border border-slate-200 bg-white p-3">
                <span class="mb-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[11px] font-bold text-white">৩</span>
                <p class=" text-xs 2xl:text-sm text-slate-600">সদস্য নিজের পোর্টাল থেকে <b>Pay</b>-তে গিয়ে টাকা জমার তথ্য সাবমিট করেন — এই ধাপে কোনো জার্নাল হয় না।</p>
            </div>
            <div class="rounded-md border border-slate-200 bg-white p-3">
                <span class="mb-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[11px] font-bold text-white">৪</span>
                <p class=" text-xs 2xl:text-sm text-slate-600">অ্যাডমিন <code class="rounded bg-slate-100 px-1 py-0.5 font-mono text-[10px] text-indigo-700">Finance → Subscription Payments</code> থেকে <b>Verify</b> করলে তবেই Receivable → Cash জার্নাল পোস্ট হয়।</p>
            </div>
        </div>

        <h3 class="mb-2 mt-4  text-xs 2xl:text-sm font-bold text-slate-800">ব্যবহৃত অ্যাকাউন্ট</h3>
        <div class="overflow-x-auto rounded-md border border-slate-200">
            <table class="w-full  text-xs 2xl:text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">Code</th>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">Account</th>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">Type</th>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">বাড়লে (Normal) মানে কী</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr><td class="px-3 py-2 text-xs 2xl:text-sm"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">1100/1200</code></td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">Cash / Bank</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">Asset</td><td class="px-3 py-2 text-slate-500">হাতে নগদ/ব্যাংক ব্যালেন্স বেড়েছে</td></tr>
                    <tr><td class="px-3 py-2 text-xs 2xl:text-sm"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">1300</code></td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">Accounts Receivable</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">Asset</td><td class="px-3 py-2 text-slate-500">সদস্যদের কাছে অ্যাসোসিয়েশনের পাওনা বেড়েছে</td></tr>
                    <tr><td class="px-3 py-2 text-xs 2xl:text-sm"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">4100</code></td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">Subscription Income</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">Income</td><td class="px-3 py-2 text-slate-500">মাসিক চাঁদা বাবদ আয় বেড়েছে</td></tr>
                    <tr><td class="px-3 py-2 text-xs 2xl:text-sm"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">4410</code></td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">Late Fine Income</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">Income</td><td class="px-3 py-2 text-slate-500">জরিমানা বাবদ আয় বেড়েছে</td></tr>
                </tbody>
            </table>
        </div>

        <div class="mt-3 rounded-md border border-violet-200 bg-violet-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-violet-800">
            <p class="mb-1 font-bold">বাস্তব উদাহরণ — শুরু থেকে শেষ পর্যন্ত</p>
            একজন সদস্যের মাসিক চাঁদা ৳1,000। দিন ১২-তে দেরির জন্য ৳50 ফাইন বসলো। প্রথমে ৳600 জমা দিলেন, পরে বাকি
            ৳450 জমা দিলেন। নিচে প্রতিটা ধাপ আলাদা করে দেখানো হলো।
        </div>

        <h3 class="mb-2 mt-4  text-xs 2xl:text-sm font-bold text-slate-800">ধাপ ১ — Due তৈরি (Accrue)</h3>
        <div class="rounded-md border border-dashed border-slate-300 bg-slate-50/60 p-3">
            <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">Journal — Type: subscription_due</p>
            <table class="w-full  text-xs 2xl:text-sm">
                <tbody>
                    <tr><td class="py-1 font-semibold text-emerald-600">Dr. Accounts Receivable (1300)</td><td class="py-1 text-right text-slate-700">৳1,000</td></tr>
                    <tr><td class="py-1 font-semibold text-red-600">Cr. Subscription Income (4100)</td><td class="py-1 text-right text-slate-700">৳1,000</td></tr>
                </tbody>
            </table>
        </div>

        <div class="mt-3 flex flex-wrap gap-3">
            <div class="min-w-[220px] flex-1 overflow-hidden rounded-md border border-slate-200">
                <div class="bg-slate-800 px-3 py-1.5  text-xs 2xl:text-sm font-bold text-white">Accounts Receivable (Asset)</div>
                <div class="flex divide-x divide-slate-200">
                    <div class="w-1/2 p-2">
                        <p class="mb-1 text-[9px] font-bold uppercase text-slate-400">Debit</p>
                        <div class="flex justify-between border-b border-dotted border-slate-200 py-0.5 text-[11px] text-slate-600"><span>Due generated</span><span>৳1,000</span></div>
                    </div>
                    <div class="w-1/2 p-2">
                        <p class="mb-1 text-[9px] font-bold uppercase text-slate-400">Credit</p>
                    </div>
                </div>
                <div class="flex justify-between bg-slate-50 px-3 py-1.5  text-xs 2xl:text-sm font-bold text-slate-700"><span>Balance</span><span>৳1,000 Dr</span></div>
            </div>
            <div class="min-w-[220px] flex-1 overflow-hidden rounded-md border border-slate-200">
                <div class="bg-slate-800 px-3 py-1.5  text-xs 2xl:text-sm font-bold text-white">Subscription Income</div>
                <div class="flex divide-x divide-slate-200">
                    <div class="w-1/2 p-2">
                        <p class="mb-1 text-[9px] font-bold uppercase text-slate-400">Debit</p>
                    </div>
                    <div class="w-1/2 p-2">
                        <p class="mb-1 text-[9px] font-bold uppercase text-slate-400">Credit</p>
                        <div class="flex justify-between border-b border-dotted border-slate-200 py-0.5 text-[11px] text-slate-600"><span>Due generated</span><span>৳1,000</span></div>
                    </div>
                </div>
                <div class="flex justify-between bg-slate-50 px-3 py-1.5  text-xs 2xl:text-sm font-bold text-slate-700"><span>Balance</span><span>৳1,000 Cr</span></div>
            </div>
        </div>
        <div class="mt-3 rounded-md border border-indigo-200 bg-indigo-50/50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-indigo-700">
            এই মুহূর্তে due-এর <code class="rounded bg-white px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">status = 'unpaid'</code>,
            <code class="rounded bg-white px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">paid_amount = 0</code>।
            Cash/Bank-এ কোনো পরিবর্তন নেই — শুধু Receivable আর Income বেড়েছে।
        </div>

        <h3 class="mb-2 mt-4  text-xs 2xl:text-sm font-bold text-slate-800">ধাপ ২ — Late Fine প্রয়োগ</h3>
        <div class="rounded-md border border-dashed border-slate-300 bg-slate-50/60 p-3">
            <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">Journal — Type: subscription_fine</p>
            <table class="w-full  text-xs 2xl:text-sm">
                <tbody>
                    <tr><td class="py-1 font-semibold text-emerald-600">Dr. Accounts Receivable (1300)</td><td class="py-1 text-right text-slate-700">৳50</td></tr>
                    <tr><td class="py-1 font-semibold text-red-600">Cr. Late Fine Income (4410)</td><td class="py-1 text-right text-slate-700">৳50</td></tr>
                </tbody>
            </table>
        </div>
        <p class="mt-2  text-xs 2xl:text-sm leading-6 text-slate-600">
            due-এর মোট এমাউন্ট এখন ৳1,000 + ৳50 = <b>৳1,050</b>, স্ট্যাটাস হয়ে যায়
            <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">overdue</code>।
            এই ফাইনটা একবারই বসে — একই due-এর জন্য দ্বিতীয়বার ফাইন বসবে না। সময়মতো জমা দিলে এই ধাপটা ঘটবেই না।
        </p>

        <h3 class="mb-2 mt-4  text-xs 2xl:text-sm font-bold text-slate-800">ধাপ ৩ — Payment Submit (কোনো জার্নাল নেই)</h3>
        <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-amber-800">
            সদস্য/এডমিন পেমেন্ট সাবমিট করলে শুধু <code class="rounded bg-white px-1.5 py-0.5 font-mono text-[11px] text-amber-700">subscription_payments</code>
            টেবিলে একটা Pending রো তৈরি হয়। এই ধাপে <b>কোনো Debit/Credit এন্ট্রি পোস্ট হয় না</b> — Receivable, Income,
            Cash কোনোটাই বদলায় না। এটা শুধু "সদস্য দাবি করছে সে টাকা দিয়েছে", যাচাই বাকি। সদস্যকে একটি প্রিন্টযোগ্য
            Receipt/Acknowledgement স্লিপ দেখানো হয়।
        </div>

        <h3 class="mb-2 mt-4  text-xs 2xl:text-sm font-bold text-slate-800">ধাপ ৪ — Payment Verify (আংশিক, ৳600)</h3>
        <div class="rounded-md border border-dashed border-slate-300 bg-slate-50/60 p-3">
            <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">Journal — Type: subscription_payment</p>
            <table class="w-full  text-xs 2xl:text-sm">
                <tbody>
                    <tr><td class="py-1 font-semibold text-emerald-600">Dr. Cash / Bank (1100/1200)</td><td class="py-1 text-right text-slate-700">৳600</td></tr>
                    <tr><td class="py-1 font-semibold text-red-600">Cr. Accounts Receivable (1300)</td><td class="py-1 text-right text-slate-700">৳600</td></tr>
                </tbody>
            </table>
        </div>
        <p class="mt-2  text-xs 2xl:text-sm leading-6 text-slate-600">
            <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">paid_amount = 600</code>,
            <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">amount = 1,050</code>
            → যেহেতু paid_amount &lt; amount, তাই
            <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">status = 'partial'</code>।
            Receivable কমে দাঁড়ালো ৳1,050 − ৳600 = <b>৳450</b>।
        </p>
        <div class="mt-3 flex flex-wrap gap-3">
            <div class="min-w-[220px] flex-1 overflow-hidden rounded-md border border-slate-200">
                <div class="bg-slate-800 px-3 py-1.5  text-xs 2xl:text-sm font-bold text-white">Accounts Receivable</div>
                <div class="flex divide-x divide-slate-200">
                    <div class="w-1/2 p-2">
                        <p class="mb-1 text-[9px] font-bold uppercase text-slate-400">Debit</p>
                        <div class="flex justify-between border-b border-dotted border-slate-200 py-0.5 text-[11px] text-slate-600"><span>Due</span><span>1,000</span></div>
                        <div class="flex justify-between border-b border-dotted border-slate-200 py-0.5 text-[11px] text-slate-600"><span>Fine</span><span>50</span></div>
                    </div>
                    <div class="w-1/2 p-2">
                        <p class="mb-1 text-[9px] font-bold uppercase text-slate-400">Credit</p>
                        <div class="flex justify-between border-b border-dotted border-slate-200 py-0.5 text-[11px] text-slate-600"><span>Payment verified</span><span>600</span></div>
                    </div>
                </div>
                <div class="flex justify-between bg-slate-50 px-3 py-1.5  text-xs 2xl:text-sm font-bold text-slate-700"><span>Balance</span><span>৳450 Dr</span></div>
            </div>
            <div class="min-w-[220px] flex-1 overflow-hidden rounded-md border border-slate-200">
                <div class="bg-slate-800 px-3 py-1.5  text-xs 2xl:text-sm font-bold text-white">Cash / Bank</div>
                <div class="flex divide-x divide-slate-200">
                    <div class="w-1/2 p-2">
                        <p class="mb-1 text-[9px] font-bold uppercase text-slate-400">Debit</p>
                        <div class="flex justify-between border-b border-dotted border-slate-200 py-0.5 text-[11px] text-slate-600"><span>Payment verified</span><span>600</span></div>
                    </div>
                    <div class="w-1/2 p-2">
                        <p class="mb-1 text-[9px] font-bold uppercase text-slate-400">Credit</p>
                    </div>
                </div>
                <div class="flex justify-between bg-slate-50 px-3 py-1.5  text-xs 2xl:text-sm font-bold text-slate-700"><span>Balance</span><span>৳600 Dr</span></div>
            </div>
        </div>
        <div class="mt-3 rounded-md border border-indigo-200 bg-indigo-50/50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-indigo-700">
            📌 Subscription Income / Fine Income-এ এই ধাপে কোনো পরিবর্তন নেই — সেগুলো ইতিমধ্যে ধাপ ১ ও ২-তেই বসে
            গেছে। এখানে শুধু Receivable → Cash কনভার্ট হচ্ছে।
        </div>

        <h3 class="mb-2 mt-4  text-xs 2xl:text-sm font-bold text-slate-800">ধাপ ৫ — Payment Verify (বাকি ৳450) → পুরোপুরি Paid</h3>
        <div class="rounded-md border border-dashed border-slate-300 bg-slate-50/60 p-3">
            <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">Journal — Type: subscription_payment</p>
            <table class="w-full  text-xs 2xl:text-sm">
                <tbody>
                    <tr><td class="py-1 font-semibold text-emerald-600">Dr. Cash / Bank</td><td class="py-1 text-right text-slate-700">৳450</td></tr>
                    <tr><td class="py-1 font-semibold text-red-600">Cr. Accounts Receivable</td><td class="py-1 text-right text-slate-700">৳450</td></tr>
                </tbody>
            </table>
        </div>
        <p class="mt-2  text-xs 2xl:text-sm leading-6 text-slate-600">
            <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">paid_amount = 600 + 450 = 1,050 = amount</code>
            → <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">status = 'paid'</code>।
            Receivable এখন <b>৳0</b> — এই due সম্পূর্ণ সেটেল। এরপর Dashboard-এ Total Due দেখলে এই due আর outstanding
            হিসেবে গণনায় আসবে না।
        </p>

        <h3 class="mb-2 mt-4  text-xs 2xl:text-sm font-bold text-slate-800">Payment Reject হলে কী হয়</h3>
        <ul class="ml-4 list-disc space-y-1  text-xs 2xl:text-sm leading-6 text-slate-600">
            <li><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">subscription_payments.status → 'rejected'</code>, সাথে reject করার কারণ সেভ হয়।</li>
            <li><b>কোনো জার্নাল রিভার্স করার দরকার নেই</b> — কারণ Verify করার আগ পর্যন্ত কোনো জার্নালই পোস্ট হয়নি (ধাপ ৩ দ্রষ্টব্য)।</li>
            <li>due-এর <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">paid_amount</code>/<code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">status</code> অপরিবর্তিত থাকে — সদস্যকে আবার নতুন করে সঠিক পেমেন্ট সাবমিট করতে হবে।</li>
        </ul>

        <h3 class="mb-2 mt-4  text-xs 2xl:text-sm font-bold text-slate-800">সম্পূর্ণ টাইমলাইন — নাম্বার সহ</h3>
        <div class="ml-2 space-y-4 border-l-2 border-indigo-200 pl-4">
            <div class="relative">
                <span class="absolute -left-[21px] top-1 h-2.5 w-2.5 rounded-full bg-indigo-600 ring-4 ring-indigo-100"></span>
                <p class="text-[10px] font-bold uppercase tracking-wide text-indigo-600">দিন ১</p>
                <p class=" text-xs 2xl:text-sm font-bold text-slate-800">Member Approve → Due Generate</p>
                <p class=" text-xs 2xl:text-sm text-slate-500">Receivable +1,000 · Subscription Income +1,000 · Cash অপরিবর্তিত</p>
            </div>
            <div class="relative">
                <span class="absolute -left-[21px] top-1 h-2.5 w-2.5 rounded-full bg-indigo-600 ring-4 ring-indigo-100"></span>
                <p class="text-[10px] font-bold uppercase tracking-wide text-indigo-600">দিন ১২ (due day পার)</p>
                <p class=" text-xs 2xl:text-sm font-bold text-slate-800">Late Fine প্রয়োগ (+৳50)</p>
                <p class=" text-xs 2xl:text-sm text-slate-500">Receivable +50 (মোট 1,050) · Fine Income +50</p>
            </div>
            <div class="relative">
                <span class="absolute -left-[21px] top-1 h-2.5 w-2.5 rounded-full bg-indigo-600 ring-4 ring-indigo-100"></span>
                <p class="text-[10px] font-bold uppercase tracking-wide text-indigo-600">দিন ১৫</p>
                <p class=" text-xs 2xl:text-sm font-bold text-slate-800">৳600 পেমেন্ট সাবমিট + Verify</p>
                <p class=" text-xs 2xl:text-sm text-slate-500">Cash +600 · Receivable −600 (বাকি 450) · status: partial</p>
            </div>
            <div class="relative">
                <span class="absolute -left-[21px] top-1 h-2.5 w-2.5 rounded-full bg-indigo-600 ring-4 ring-indigo-100"></span>
                <p class="text-[10px] font-bold uppercase tracking-wide text-indigo-600">দিন ২০</p>
                <p class=" text-xs 2xl:text-sm font-bold text-slate-800">বাকি ৳450 পেমেন্ট সাবমিট + Verify</p>
                <p class=" text-xs 2xl:text-sm text-slate-500">Cash +450 · Receivable −450 (বাকি 0) · status: paid ✅</p>
            </div>
        </div>

        <div class="mt-3 overflow-x-auto rounded-md border border-slate-200">
            <table class="w-full  text-xs 2xl:text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">তারিখ</th>
                        <th class="px-3 py-2 text-right font-semibold uppercase tracking-wide text-slate-500">Receivable</th>
                        <th class="px-3 py-2 text-right font-semibold uppercase tracking-wide text-slate-500">Cash/Bank</th>
                        <th class="px-3 py-2 text-right font-semibold uppercase tracking-wide text-slate-500">Subscription Income (cumulative)</th>
                        <th class="px-3 py-2 text-right font-semibold uppercase tracking-wide text-slate-500">Fine Income (cumulative)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">দিন ১ (due তৈরি)</td><td class="px-3 py-2 text-right text-slate-600">1,000</td><td class="px-3 py-2 text-right text-slate-600">0</td><td class="px-3 py-2 text-right text-slate-600">1,000</td><td class="px-3 py-2 text-right text-slate-600">0</td></tr>
                    <tr><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">দিন ১২ (fine)</td><td class="px-3 py-2 text-right text-slate-600">1,050</td><td class="px-3 py-2 text-right text-slate-600">0</td><td class="px-3 py-2 text-right text-slate-600">1,000</td><td class="px-3 py-2 text-right text-slate-600">50</td></tr>
                    <tr><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">দিন ১৫ (৳600 verify)</td><td class="px-3 py-2 text-right text-slate-600">450</td><td class="px-3 py-2 text-right text-slate-600">600</td><td class="px-3 py-2 text-right text-slate-600">1,000</td><td class="px-3 py-2 text-right text-slate-600">50</td></tr>
                    <tr><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">দিন ২০ (৳450 verify)</td><td class="px-3 py-2 text-right text-slate-600">0</td><td class="px-3 py-2 text-right text-slate-600">1,050</td><td class="px-3 py-2 text-right text-slate-600">1,000</td><td class="px-3 py-2 text-right text-slate-600">50</td></tr>
                </tbody>
            </table>
        </div>

        <div class="mt-3 rounded-md border border-indigo-200 bg-indigo-50/50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-indigo-700">
            <p class="mb-1 font-bold">নতুন ফিচার — পেমেন্ট রসিদ</p>
            সদস্য পেমেন্ট সাবমিট করার সাথে সাথে একটি প্রিন্টযোগ্য <b>Receipt</b> তৈরি হয় (Receipt No, সদস্যের নাম/কোড,
            মাস, পরিমাণ, পেমেন্ট মেথড, স্ট্যাটাস সহ)। এটি প্রমাণস্বরূপ প্রিন্ট করে রাখা যায়, যদিও প্রকৃত জার্নাল
            অ্যাডমিন ভেরিফাই করার পরই পোস্ট হয়।
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- 7. CHARGES --}}
    {{-- ============================================================ --}}
    <div id="charges" class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[12px] font-bold text-white">৭</span>
            <h2 class="text-sm font-bold text-slate-800">সদস্য চার্জ (Member Charges)</h2>
        </div>

        <p class=" text-xs 2xl:text-sm leading-6 text-slate-600">
            চাঁদা ছাড়া অন্য যেকোনো নির্দিষ্ট চার্জ (যেমন — ভর্তি ফি, ফরম ফি, জরিমানা) সদস্যের নামে ধার্য করা হলে
            একইভাবে Receivable বাড়ে, ঠিক subscription due-এর মতোই accrual নিয়মে আয় ধরা হয়।
        </p>

        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            <div class="rounded-md border border-dashed border-slate-300 bg-slate-50/60 p-3">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">Journal — Charge ধার্য করার সময়</p>
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Accounts Receivable (1300)</td><td class="py-1 text-right text-slate-700">চার্জের পরিমাণ</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. প্রযোজ্য Income Account</td><td class="py-1 text-right text-slate-700">চার্জের পরিমাণ</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="rounded-md border border-dashed border-slate-300 bg-slate-50/60 p-3">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">Journal — Charge Payment গ্রহণের সময়</p>
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Cash / Bank</td><td class="py-1 text-right text-slate-700">প্রাপ্ত টাকা</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Accounts Receivable (1300)</td><td class="py-1 text-right text-slate-700">প্রাপ্ত টাকা</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3 rounded-md border border-violet-200 bg-violet-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-violet-800">
            <p class="mb-1 font-bold">বাস্তব উদাহরণ — ভর্তি ফি ৳500</p>
            নতুন সদস্যের ভর্তির সময় ৳500 Admission Fee ধার্য করা হলো:
            <div class="mt-2 rounded-md border border-dashed border-violet-300 bg-white p-3">
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Accounts Receivable</td><td class="py-1 text-right text-slate-700">৳500</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Other Income (4900 — Admission Fee)</td><td class="py-1 text-right text-slate-700">৳500</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-2">সদস্য পুরো ৳500 নগদ পরিশোধ করলে:</p>
            <div class="mt-2 rounded-md border border-dashed border-violet-300 bg-white p-3">
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Cash</td><td class="py-1 text-right text-slate-700">৳500</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Accounts Receivable</td><td class="py-1 text-right text-slate-700">৳500</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-2">ফলাফল: Cash +500, Receivable ফিরে 0, Income +500 (ধার্য করার দিনই ধরা হয়েছিল)।</p>
        </div>

        <p class="mt-3  text-xs 2xl:text-sm leading-6 text-slate-600">
            কোনো চার্জ মাফ (Waive) করা হলে বা পেমেন্ট বাতিল করা হলে সিস্টেম স্বয়ংক্রিয়ভাবে একটি বিপরীত (Reversal)
            জার্নাল পোস্ট করে — Receivable ও Income উভয়ই কমে যায়, যেন চার্জটা কখনো ধার্যই হয়নি।
        </p>
    </div>

    {{-- ============================================================ --}}
    {{-- 8. LOANS --}}
    {{-- ============================================================ --}}
    <div id="loans" class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[12px] font-bold text-white">৮</span>
            <h2 class="text-sm font-bold text-slate-800">ঋণ (Loans) — বিস্তারিত ফ্লো</h2>
        </div>

        <h3 class="mb-2  text-xs 2xl:text-sm font-bold text-slate-800">ঋণ প্রদান (Disbursement)</h3>
        <div class="rounded-md border border-dashed border-slate-300 bg-slate-50/60 p-3">
            <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">Journal — Loan Disbursement</p>
            <table class="w-full  text-xs 2xl:text-sm">
                <tbody>
                    <tr><td class="py-1 font-semibold text-emerald-600">Dr. Member Loan Receivable (1310)</td><td class="py-1 text-right text-slate-700">ঋণের পরিমাণ</td></tr>
                    <tr><td class="py-1 font-semibold text-red-600">Cr. Cash / Bank</td><td class="py-1 text-right text-slate-700">ঋণের পরিমাণ</td></tr>
                </tbody>
            </table>
        </div>

        <h3 class="mb-2 mt-4  text-xs 2xl:text-sm font-bold text-slate-800">কিস্তি পরিশোধ (Repayment)</h3>
        <div class="rounded-md border border-dashed border-slate-300 bg-slate-50/60 p-3">
            <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">Journal — Loan Repayment (মূল + সুদ)</p>
            <table class="w-full  text-xs 2xl:text-sm">
                <tbody>
                    <tr><td class="py-1 font-semibold text-emerald-600">Dr. Cash / Bank</td><td class="py-1 text-right text-slate-700">মোট প্রাপ্ত কিস্তি</td></tr>
                    <tr><td class="py-1 font-semibold text-red-600">Cr. Member Loan Receivable (1310)</td><td class="py-1 text-right text-slate-700">মূল অংশ (Principal)</td></tr>
                    <tr><td class="py-1 font-semibold text-red-600">Cr. Loan Interest Income (4500)</td><td class="py-1 text-right text-slate-700">সুদ অংশ (Interest)</td></tr>
                </tbody>
            </table>
        </div>

        <div class="mt-3 rounded-md border border-violet-200 bg-violet-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-violet-800">
            <p class="mb-1 font-bold">বাস্তব উদাহরণ — ৳50,000 ঋণ, ৳5,000×১০ কিস্তি (৫% ফ্ল্যাট সুদ)</p>
            একজন সদস্য ৳50,000 ঋণ নিলেন, মাসিক কিস্তি ৳5,500 (মূল ৳5,000 + সুদ ৳500), ১০ মাসে শোধ করবেন।
            <div class="mt-2 rounded-md border border-dashed border-violet-300 bg-white p-3">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">দিন ১ — ঋণ প্রদান</p>
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Member Loan Receivable</td><td class="py-1 text-right text-slate-700">৳50,000</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Cash / Bank</td><td class="py-1 text-right text-slate-700">৳50,000</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-2 rounded-md border border-dashed border-violet-300 bg-white p-3">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">১ম মাস কিস্তি</p>
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Cash / Bank</td><td class="py-1 text-right text-slate-700">৳5,500</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Member Loan Receivable</td><td class="py-1 text-right text-slate-700">৳5,000</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Loan Interest Income</td><td class="py-1 text-right text-slate-700">৳500</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-3 overflow-x-auto rounded-md border border-violet-200">
                <table class="w-full bg-white  text-xs 2xl:text-sm">
                    <thead class="border-b border-violet-100 bg-violet-100/50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-violet-700">মাস</th>
                            <th class="px-3 py-2 text-right font-semibold uppercase tracking-wide text-violet-700">কিস্তির পর Loan Receivable</th>
                            <th class="px-3 py-2 text-right font-semibold uppercase tracking-wide text-violet-700">Cumulative Interest Income</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-violet-100">
                        <tr><td class="px-3 py-1.5 text-slate-600">১</td><td class="px-3 py-1.5 text-right text-slate-600">45,000</td><td class="px-3 py-1.5 text-right text-slate-600">500</td></tr>
                        <tr><td class="px-3 py-1.5 text-slate-600">২</td><td class="px-3 py-1.5 text-right text-slate-600">40,000</td><td class="px-3 py-1.5 text-right text-slate-600">1,000</td></tr>
                        <tr><td class="px-3 py-1.5 text-slate-600">...</td><td class="px-3 py-1.5 text-right text-slate-600">...</td><td class="px-3 py-1.5 text-right text-slate-600">...</td></tr>
                        <tr><td class="px-3 py-1.5 text-slate-600">১০ (শেষ কিস্তি)</td><td class="px-3 py-1.5 text-right text-slate-600">0</td><td class="px-3 py-1.5 text-right text-slate-600">5,000</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <p class="mt-3  text-xs 2xl:text-sm leading-6 text-slate-600">
            প্রতিটি কিস্তিতে মূল ও সুদ আলাদা করে হিসাব হয়, ফলে Loan Receivable ধীরে ধীরে শূন্যে নেমে আসে এবং সুদ
            Income হিসেবে প্রতি মাসে যোগ হয় — একসাথে পুরোটা নয়, তাই P&amp;L প্রতিটা মাসে সঠিক সুদ-আয় দেখায়।
        </p>
    </div>

    {{-- ============================================================ --}}
    {{-- 9. INVESTMENTS --}}
    {{-- ============================================================ --}}
    <div id="investments" class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[12px] font-bold text-white">৯</span>
            <h2 class="text-sm font-bold text-slate-800">বিনিয়োগ (Investments)</h2>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-md border border-dashed border-slate-300 bg-slate-50/60 p-3">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">Journal — বিনিয়োগ করার সময়</p>
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Investments (1400)</td><td class="py-1 text-right text-slate-700">বিনিয়োগকৃত পরিমাণ</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Cash / Bank</td><td class="py-1 text-right text-slate-700">বিনিয়োগকৃত পরিমাণ</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="rounded-md border border-dashed border-slate-300 bg-slate-50/60 p-3">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">Journal — রিটার্ন পাওয়ার সময়</p>
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Cash / Bank</td><td class="py-1 text-right text-slate-700">প্রাপ্ত রিটার্ন</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Investments (1400) — যদি মূলধন ফেরত</td><td class="py-1 text-right text-slate-700">প্রাপ্ত পরিমাণ</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Investment Income (4200) — যদি লাভাংশ</td><td class="py-1 text-right text-slate-700">প্রাপ্ত পরিমাণ</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3 rounded-md border border-violet-200 bg-violet-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-violet-800">
            <p class="mb-1 font-bold">বাস্তব উদাহরণ — ৳1,00,000 FDR, ১ বছর পর ৳8,000 লাভ + মূলধন ফেরত</p>
            <div class="mt-2 rounded-md border border-dashed border-violet-300 bg-white p-3">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">বিনিয়োগ করার সময়</p>
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Investments</td><td class="py-1 text-right text-slate-700">৳1,00,000</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Bank</td><td class="py-1 text-right text-slate-700">৳1,00,000</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-2 rounded-md border border-dashed border-violet-300 bg-white p-3">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">১ বছর পর — মূলধন + মুনাফা একসাথে ফেরত</p>
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Bank</td><td class="py-1 text-right text-slate-700">৳1,08,000</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Investments (মূলধন অংশ)</td><td class="py-1 text-right text-slate-700">৳1,00,000</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Investment Income (মুনাফা অংশ)</td><td class="py-1 text-right text-slate-700">৳8,000</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-2">ফলাফল: Investments ফিরে 0, Bank নেট +8,000 বেড়েছে, Investment Income +8,000।</p>
        </div>
        <p class="mt-3  text-xs 2xl:text-sm leading-6 text-slate-600">
            রিটার্নের ধরন (Principal বা Income) নির্বাচনের উপর নির্ভর করে সিস্টেম স্বয়ংক্রিয়ভাবে সঠিক অ্যাকাউন্টে
            ক্রেডিট করে — তাই এন্ট্রি করার সময় সাবধানে নির্বাচন করা জরুরি, নাহলে মূলধন ভুলে আয় হিসেবে দেখানো হতে পারে।
        </p>
    </div>

    {{-- ============================================================ --}}
    {{-- 10. SHARES --}}
    {{-- ============================================================ --}}
    <div id="shares" class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[12px] font-bold text-white">১০</span>
            <h2 class="text-sm font-bold text-slate-800">শেয়ার (Member Shares)</h2>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-md border border-dashed border-slate-300 bg-slate-50/60 p-3">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">Journal — শেয়ার ক্রয়</p>
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Cash / Bank</td><td class="py-1 text-right text-slate-700">শেয়ারের মূল্য</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Member Share Capital (3300)</td><td class="py-1 text-right text-slate-700">শেয়ারের মূল্য</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="rounded-md border border-dashed border-slate-300 bg-slate-50/60 p-3">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">Journal — সদস্য Exit / শেয়ার ফেরত</p>
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Member Share Capital (3300)</td><td class="py-1 text-right text-slate-700">ফেরতযোগ্য পরিমাণ</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Cash / Bank</td><td class="py-1 text-right text-slate-700">ফেরতযোগ্য পরিমাণ</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3 rounded-md border border-violet-200 bg-violet-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-violet-800">
            <p class="mb-1 font-bold">বাস্তব উদাহরণ — ১০টি শেয়ার @৳500 = ৳5,000</p>
            একজন সদস্য ১০টি শেয়ার কিনলেন, প্রতিটি ৳500:
            <div class="mt-2 rounded-md border border-dashed border-violet-300 bg-white p-3">
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Cash</td><td class="py-1 text-right text-slate-700">৳5,000</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Member Share Capital</td><td class="py-1 text-right text-slate-700">৳5,000</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-2">পরে সংগঠন ত্যাগ করার সময় পুরো ৳5,000 ফেরত পেলে:</p>
            <div class="mt-2 rounded-md border border-dashed border-violet-300 bg-white p-3">
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Member Share Capital</td><td class="py-1 text-right text-slate-700">৳5,000</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Cash / Bank</td><td class="py-1 text-right text-slate-700">৳5,000</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-2">ফলাফল: Equity (Share Capital) নিট পরিবর্তন 0, Cash নিট পরিবর্তন 0 — শেয়ার-হোল্ডিং এর জীবনচক্র সম্পূর্ণ হলো।</p>
        </div>
        <p class="mt-3  text-xs 2xl:text-sm leading-6 text-slate-600">
            সদস্যরা প্রতিষ্ঠানের মূলধনে (Equity) অংশ নিলে তা Member Share Capital-এ যোগ হয়। Dashboard-এর
            "Total Deposited" হিসাবে সক্রিয় (এখনো ফেরত না নেওয়া) শেয়ারের মূল্যও যোগ হয়।
        </p>
    </div>

    {{-- ============================================================ --}}
    {{-- 11. ASSETS --}}
    {{-- ============================================================ --}}
    <div id="assets" class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[12px] font-bold text-white">১১</span>
            <h2 class="text-sm font-bold text-slate-800">সম্পদ ও অবচয় (Assets &amp; Depreciation)</h2>
        </div>

        <h3 class="mb-2  text-xs 2xl:text-sm font-bold text-slate-800">সম্পদ ক্রয় (Asset Purchase)</h3>
        <div class="rounded-md border border-dashed border-slate-300 bg-slate-50/60 p-3">
            <table class="w-full  text-xs 2xl:text-sm">
                <tbody>
                    <tr><td class="py-1 font-semibold text-emerald-600">Dr. Fixed Assets (নির্দিষ্ট সাব-ক্যাটাগরি, যেমন 1520 Office Equipment)</td><td class="py-1 text-right text-slate-700">ক্রয়মূল্য</td></tr>
                    <tr><td class="py-1 font-semibold text-red-600">Cr. Cash / Bank</td><td class="py-1 text-right text-slate-700">ক্রয়মূল্য</td></tr>
                </tbody>
            </table>
        </div>

        <h3 class="mb-2 mt-4  text-xs 2xl:text-sm font-bold text-slate-800">মাসিক/বার্ষিক অবচয় (Depreciation)</h3>
        <div class="rounded-md border border-dashed border-slate-300 bg-slate-50/60 p-3">
            <table class="w-full  text-xs 2xl:text-sm">
                <tbody>
                    <tr><td class="py-1 font-semibold text-emerald-600">Dr. Depreciation Expense</td><td class="py-1 text-right text-slate-700">অবচয়ের পরিমাণ</td></tr>
                    <tr><td class="py-1 font-semibold text-red-600">Cr. Fixed Asset / Accumulated Depreciation</td><td class="py-1 text-right text-slate-700">অবচয়ের পরিমাণ</td></tr>
                </tbody>
            </table>
        </div>

        <div class="mt-3 rounded-md border border-violet-200 bg-violet-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-violet-800">
            <p class="mb-1 font-bold">বাস্তব উদাহরণ — ৳60,000-এর ল্যাপটপ, ৫ বছরে Straight-Line Depreciation</p>
            ল্যাপটপ কেনা হলো ৳60,000 দিয়ে, জীবনকাল ৫ বছর ধরা হলো (বার্ষিক অবচয় ৳12,000, মাসিক ৳1,000):
            <div class="mt-2 rounded-md border border-dashed border-violet-300 bg-white p-3">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">ক্রয়ের সময়</p>
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Fixed Assets — Office Equipment</td><td class="py-1 text-right text-slate-700">৳60,000</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Bank</td><td class="py-1 text-right text-slate-700">৳60,000</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-2 rounded-md border border-dashed border-violet-300 bg-white p-3">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">প্রতি মাসে</p>
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Depreciation Expense</td><td class="py-1 text-right text-slate-700">৳1,000</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Fixed Asset (বইমূল্য কমে)</td><td class="py-1 text-right text-slate-700">৳1,000</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-2">১২ মাস পর ল্যাপটপের বইমূল্য (Book Value) দাঁড়াবে ৳60,000 − ৳12,000 = <b>৳48,000</b>, এবং P&amp;L-এ বছরে ৳12,000 Depreciation Expense যোগ হবে।</p>
        </div>

        <h3 class="mb-2 mt-4  text-xs 2xl:text-sm font-bold text-slate-800">সম্পদ বিক্রয়/অপসারণ (Disposal / Sale)</h3>
        <div class="rounded-md border border-dashed border-slate-300 bg-slate-50/60 p-3">
            <table class="w-full  text-xs 2xl:text-sm">
                <tbody>
                    <tr><td class="py-1 font-semibold text-emerald-600">Dr. Cash / Bank (বিক্রয়মূল্য থাকলে)</td><td class="py-1 text-right text-slate-700">—</td></tr>
                    <tr><td class="py-1 font-semibold text-red-600">Cr. Fixed Asset (বাকি বইমূল্য)</td><td class="py-1 text-right text-slate-700">—</td></tr>
                    <tr><td class="py-1 text-slate-600">+ পার্থক্য অনুযায়ী <span class="font-semibold text-emerald-600">Loss on Disposal (5910)</span> অথবা <span class="font-semibold text-red-600">Gain on Disposal (4910)</span></td><td class="py-1 text-right text-slate-700">—</td></tr>
                </tbody>
            </table>
        </div>
        <div class="mt-3 rounded-md border border-violet-200 bg-violet-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-violet-800">
            উপরের ল্যাপটপ যদি ৩ বছর পর (বইমূল্য ৳60,000 − ৳36,000 = ৳24,000) ৳20,000-এ বিক্রি হয়:
            <div class="mt-2 rounded-md border border-dashed border-violet-300 bg-white p-3">
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Cash</td><td class="py-1 text-right text-slate-700">৳20,000</td></tr>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Loss on Disposal (5910)</td><td class="py-1 text-right text-slate-700">৳4,000</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Fixed Asset</td><td class="py-1 text-right text-slate-700">৳24,000</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-2">কারণ বিক্রয়মূল্য (২০,০০০) বইমূল্যের (২৪,০০০) চেয়ে কম, তাই ৪,০০০ টাকা Loss হিসেবে ধরা হলো।</p>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- 12. TELLER --}}
    {{-- ============================================================ --}}
    <div id="teller" class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[12px] font-bold text-white">১২</span>
            <h2 class="text-sm font-bold text-slate-800">টেলার / নগদ ব্যবস্থাপনা (Teller)</h2>
        </div>

        <p class=" text-xs 2xl:text-sm leading-6 text-slate-600">
            যেকোনো ধরনের সরাসরি নগদ গ্রহণ (Receive) বা প্রদান (Payment) — যা অন্য কোনো নির্দিষ্ট মডিউলের
            (চাঁদা/চার্জ) অংশ নয় — টেলার মডিউল দিয়ে রেকর্ড করা হয়। প্রতিদিনের কাজ শুরুর আগে টেলার "Open" করতে হয়
            এবং দিনশেষে "Closing" করে ক্যাশ মিলিয়ে নিতে হয়।
        </p>

        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            <div class="rounded-md border border-dashed border-slate-300 bg-slate-50/60 p-3">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">Journal — Teller Receive</p>
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Cash</td><td class="py-1 text-right text-slate-700">প্রাপ্ত টাকা</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. নির্বাচিত Counter Account</td><td class="py-1 text-right text-slate-700">প্রাপ্ত টাকা</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="rounded-md border border-dashed border-slate-300 bg-slate-50/60 p-3">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">Journal — Teller Payment</p>
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. নির্বাচিত Counter Account</td><td class="py-1 text-right text-slate-700">প্রদত্ত টাকা</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Cash</td><td class="py-1 text-right text-slate-700">প্রদত্ত টাকা</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3 rounded-md border border-violet-200 bg-violet-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-violet-800">
            <p class="mb-1 font-bold">বাস্তব উদাহরণ — একদিনের টেলার</p>
            সকালে টেলার Open করা হলো ওপেনিং ব্যালেন্স ৳5,000 দিয়ে। দিনের মধ্যে ৳2,000 নগদ অনুদান জমা হলো এবং ৳800
            চা-নাস্তার খরচ দেওয়া হলো। দিনশেষে গণনা করে দেখা গেল হাতে আছে ৳6,200।
            <div class="mt-2 overflow-x-auto rounded-md border border-violet-200">
                <table class="w-full bg-white  text-xs 2xl:text-sm">
                    <thead class="border-b border-violet-100 bg-violet-100/50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-violet-700">ইভেন্ট</th>
                            <th class="px-3 py-2 text-right font-semibold uppercase tracking-wide text-violet-700">প্রভাব</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-violet-100">
                        <tr><td class="px-3 py-1.5 text-slate-600">ওপেনিং ব্যালেন্স</td><td class="px-3 py-1.5 text-right text-slate-600">৳5,000</td></tr>
                        <tr><td class="px-3 py-1.5 text-slate-600">নগদ অনুদান গ্রহণ (Teller Receive)</td><td class="px-3 py-1.5 text-right text-slate-600">+৳2,000</td></tr>
                        <tr><td class="px-3 py-1.5 text-slate-600">নাস্তার খরচ (Teller Payment)</td><td class="px-3 py-1.5 text-right text-slate-600">−৳800</td></tr>
                        <tr><td class="px-3 py-1.5 font-bold text-slate-800">প্রত্যাশিত ক্লোজিং ব্যালেন্স</td><td class="px-3 py-1.5 text-right font-bold text-slate-800">৳6,200</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-2">গণনায় প্রাপ্ত টাকা এই ৳6,200-এর সাথে মিললেই টেলার নির্ভুলভাবে ক্লোজ করা যাবে; না মিললে গরমিলের কারণ খুঁজে বের করতে হবে।</p>
        </div>
        <div class="mt-3 rounded-md border border-amber-200 bg-amber-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-amber-800">
            <p class="mb-1 font-bold">মনে রাখবেন</p>
            টেলার ক্লোজিং না করা পর্যন্ত পরের দিনের জন্য নতুন টেলার সাধারণত খোলা যায় না — এটি ক্যাশ গরমিল ঠেকাতে সাহায্য করে।
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- 13. INCOME / EXPENSE --}}
    {{-- ============================================================ --}}
    <div id="income-expense" class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[12px] font-bold text-white">১৩</span>
            <h2 class="text-sm font-bold text-slate-800">আয় ও ব্যয় (Direct Income &amp; Expense)</h2>
        </div>

        <p class=" text-xs 2xl:text-sm leading-6 text-slate-600">
            চাঁদা/চার্জ ছাড়া অন্য যেকোনো সরাসরি আয় (যেমন — অনুদান) বা ব্যয় (যেমন — অফিস ভাড়া, বিদ্যুৎ বিল)
            <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">Finance → Income</code>
            ও <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">Finance → Expenses</code>
            মেনু থেকে সরাসরি এন্ট্রি করা যায়।
        </p>

        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            <div class="rounded-md border border-dashed border-slate-300 bg-slate-50/60 p-3">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">Journal — Income Entry</p>
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Cash / Bank</td><td class="py-1 text-right text-slate-700">প্রাপ্ত পরিমাণ</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. নির্বাচিত Income Account</td><td class="py-1 text-right text-slate-700">প্রাপ্ত পরিমাণ</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="rounded-md border border-dashed border-slate-300 bg-slate-50/60 p-3">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">Journal — Expense Entry</p>
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. নির্বাচিত Expense Account</td><td class="py-1 text-right text-slate-700">খরচের পরিমাণ</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Cash / Bank</td><td class="py-1 text-right text-slate-700">খরচের পরিমাণ</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3 rounded-md border border-violet-200 bg-violet-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-violet-800">
            <p class="mb-1 font-bold">বাস্তব উদাহরণ</p>
            একজন দাতা ব্যাংকের মাধ্যমে ৳10,000 অনুদান দিলেন, একই সপ্তাহে অফিস ভাড়া বাবদ ৳15,000 নগদে পরিশোধ করা হলো:
            <div class="mt-2 rounded-md border border-dashed border-violet-300 bg-white p-3">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">অনুদান গ্রহণ</p>
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Bank</td><td class="py-1 text-right text-slate-700">৳10,000</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Donation Income (4300)</td><td class="py-1 text-right text-slate-700">৳10,000</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-2 rounded-md border border-dashed border-violet-300 bg-white p-3">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">অফিস ভাড়া পরিশোধ</p>
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Office Expense (5100)</td><td class="py-1 text-right text-slate-700">৳15,000</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Cash</td><td class="py-1 text-right text-slate-700">৳15,000</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-2">এই মাসে নিট প্রভাব: আয় +10,000, ব্যয় +15,000 → P&amp;L-এ নিট ৳5,000 ঘাটতি (deficit) দেখাবে।</p>
        </div>
        <p class="mt-3  text-xs 2xl:text-sm leading-6 text-slate-600">ভুল এন্ট্রি করা হলে "Reverse"/"Cancel" অপশন দিয়ে বিপরীত জার্নাল পোস্ট করা যায় (মূল এন্ট্রি ইতিহাসে থেকে যায়)।</p>
    </div>

    {{-- ============================================================ --}}
    {{-- 14. MANUAL JOURNAL --}}
    {{-- ============================================================ --}}
    <div id="manual-journal" class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[12px] font-bold text-white">১৪</span>
            <h2 class="text-sm font-bold text-slate-800">ম্যানুয়াল জার্নাল ও রিভার্সাল</h2>
        </div>

        <p class=" text-xs 2xl:text-sm leading-6 text-slate-600">
            <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">Finance → Journal Entries → Manual Journal</code>
            থেকে যেকোনো কাস্টম হিসাব এন্ট্রি করা যায় — এটি ব্যবহার হয় এমন পরিস্থিতিতে যা অন্য কোনো মডিউলের অংশ নয়
            (যেমন — বছর শেষের সমন্বয় এন্ট্রি, ভুল সংশোধনের বিশেষ এন্ট্রি)।
        </p>

        <h3 class="mb-2 mt-4  text-xs 2xl:text-sm font-bold text-slate-800">নিয়ম</h3>
        <ul class="ml-4 list-disc space-y-1  text-xs 2xl:text-sm leading-6 text-slate-600">
            <li>প্রতিটি লাইনে হয় Debit, নাহয় Credit থাকবে — দুটো একসাথে নয়।</li>
            <li>কমপক্ষে ২টি লাইন থাকতে হবে এবং মোট Debit অবশ্যই মোট Credit-এর সমান হতে হবে, নাহলে "Not Balanced" দেখাবে এবং পোস্ট করা যাবে না।</li>
            <li>একবার Post হয়ে গেলে জার্নাল আর এডিট/ডিলিট করা যায় না।</li>
        </ul>

        <div class="mt-3 rounded-md border border-violet-200 bg-violet-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-violet-800">
            <p class="mb-1 font-bold">বাস্তব উদাহরণ — বছরের শুরুতে ওপেনিং ব্যালেন্স বসানো</p>
            নতুন হিসাব বছর শুরুর সময় ব্যাংকে ৳2,00,000 এবং হাতে নগদ ৳15,000 ছিল বলে সিস্টেমে ওপেনিং ব্যালেন্স
            বসাতে হবে — একটি ম্যানুয়াল জার্নাল দিয়ে:
            <div class="mt-2 rounded-md border border-dashed border-violet-300 bg-white p-3">
                <table class="w-full  text-xs 2xl:text-sm">
                    <tbody>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Bank</td><td class="py-1 text-right text-slate-700">৳2,00,000</td></tr>
                        <tr><td class="py-1 font-semibold text-emerald-600">Dr. Cash</td><td class="py-1 text-right text-slate-700">৳15,000</td></tr>
                        <tr><td class="py-1 font-semibold text-red-600">Cr. Accumulated Fund (3200)</td><td class="py-1 text-right text-slate-700">৳2,15,000</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-2">মোট Debit (2,15,000) = মোট Credit (2,15,000), তাই এটি Balanced এবং পোস্ট করা যাবে।</p>
        </div>

        <h3 class="mb-2 mt-4  text-xs 2xl:text-sm font-bold text-slate-800">রিভার্সাল (Reverse Journal)</h3>
        <p class=" text-xs 2xl:text-sm leading-6 text-slate-600">
            কোনো Posted Manual Journal ভুল হলে "Reverse" অপশন ব্যবহার করুন — এটি মূল জার্নালের বিপরীত (Debit ↔ Credit
            অদলবদল করা) একটি নতুন জার্নাল পোস্ট করবে এবং মূল জার্নালটিকে "Reversed" হিসেবে চিহ্নিত করবে। মূল জার্নাল
            কখনো মুছে যায় না — অডিট ট্রেইলের জন্য এটি জরুরি।
        </p>

        <div class="mt-3 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-emerald-700">
            <p class="mb-1 font-bold">নতুন ফিচার — জার্নাল ভাউচার প্রিন্ট</p>
            জার্নাল লিস্টে যেকোনো এন্ট্রির পাশে <b>View</b>-তে ক্লিক করলে এখন একটি রসিদ (Voucher) আকারে বিস্তারিত
            দেখা যায় — প্রতিষ্ঠানের নাম/ঠিকানা, ভাউচার নম্বর, সব Debit/Credit লাইন, Prepared By / Authorized By সহ।
            নিচে থাকা <b>Print Receipt</b> বাটনে ক্লিক করলে সরাসরি প্রিন্ট করা যাবে।
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- 15. STATEMENTS --}}
    {{-- ============================================================ --}}
    <div id="statements" class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[12px] font-bold text-white">১৫</span>
            <h2 class="text-sm font-bold text-slate-800">ফাইন্যান্সিয়াল স্টেটমেন্টে প্রভাব</h2>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-md border border-slate-200 bg-slate-50/60 p-3">
                <h3 class="mb-1  text-xs 2xl:text-sm font-bold text-slate-800">Trial Balance</h3>
                <p class=" text-xs 2xl:text-sm leading-6 text-slate-600">প্রতিটা জার্নালের Debit = Credit থাকে বলে ট্রায়াল ব্যালেন্স সবসময় মিলে যায়। প্রতিটা এন্ট্রি অন্তত ২ লাইনের (এক Debit, এক Credit) — কোনোটাতেই ভারসাম্য নষ্ট হয় না।</p>
            </div>
            <div class="rounded-md border border-slate-200 bg-slate-50/60 p-3">
                <h3 class="mb-1  text-xs 2xl:text-sm font-bold text-slate-800">Profit &amp; Loss</h3>
                <p class=" text-xs 2xl:text-sm leading-6 text-slate-600">Subscription Income, Fine Income, Loan Interest Income ইত্যাদি সবকিছু <b>due/ইভেন্ট তৈরি হওয়ার দিনেই</b> P&amp;L-এ যোগ হয়ে যায় — সেই মাসের প্রফিটে প্রভাব ফেলে, পেমেন্ট আসুক বা না আসুক।</p>
            </div>
            <div class="rounded-md border border-slate-200 bg-slate-50/60 p-3">
                <h3 class="mb-1  text-xs 2xl:text-sm font-bold text-slate-800">Balance Sheet</h3>
                <p class=" text-xs 2xl:text-sm leading-6 text-slate-600">যতদিন due unpaid/partial থাকে, ওই টাকা Balance Sheet-এ <b>Accounts Receivable</b> (Asset) হিসেবে দেখাবে। Payment verify হলে সেটা Receivable থেকে সরে <b>Cash/Bank</b> (Asset)-এ চলে যায় — মোট Asset-এর পরিমাণ বদলায় না, শুধু ভেতরের গঠন বদলায়।</p>
            </div>
            <div class="rounded-md border border-slate-200 bg-slate-50/60 p-3">
                <h3 class="mb-1  text-xs 2xl:text-sm font-bold text-slate-800">Cash Flow (বাস্তবে)</h3>
                <p class=" text-xs 2xl:text-sm leading-6 text-slate-600">প্রকৃত টাকার নড়াচড়া হয় শুধুই Payment Verify, Loan Disbursement/Repayment, বা সরাসরি Cash/Bank জড়িত এন্ট্রির সময় — শুধু Due তৈরি বা Fine বসানোর সময় Cash Flow-তে কিছুই দেখাবে না।</p>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- 16. DASHBOARD MAP --}}
    {{-- ============================================================ --}}
    <div id="dashboard-map" class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[12px] font-bold text-white">১৬</span>
            <h2 class="text-sm font-bold text-slate-800">ড্যাশবোর্ডের সংখ্যাগুলো কোথা থেকে আসে</h2>
        </div>

        <p class=" text-xs 2xl:text-sm leading-6 text-slate-600">
            উপরের Subscription উদাহরণের দিন ১২ (Late Fine বসার পর, এখনো কোনো পেমেন্ট verify হয়নি) সময়ের অবস্থা ধরে
            নিচের ড্যাশবোর্ড লেবেলগুলো ব্যাখ্যা করা হলো:
        </p>

        <div class="mt-3 overflow-x-auto rounded-md border border-slate-200">
            <table class="w-full  text-xs 2xl:text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">ড্যাশবোর্ড লেবেল</th>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">মানে</th>
                        <th class="px-3 py-2 text-right font-semibold uppercase tracking-wide text-slate-500">দিন ১২-তে মান</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr><td class="px-3 py-2 font-semibold text-slate-700">This Month's Amount</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">চলতি মাসের due-এর নির্ধারিত মোট এমাউন্ট (paid হোক বা না হোক)</td><td class="px-3 py-2 text-right text-slate-600">৳1,050</td></tr>
                    <tr><td class="px-3 py-2 font-semibold text-slate-700">Total Due (Outstanding)</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">সব মাসের unpaid/partial/overdue due-এর বাকি অংশের যোগফল</td><td class="px-3 py-2 text-right text-slate-600">৳1,050</td></tr>
                    <tr><td class="px-3 py-2 font-semibold text-slate-700">Total Due বক্সের ভেতরের "Current Due"</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">শুধু চলতি মাসের বাকি অংশ (outstanding)</td><td class="px-3 py-2 text-right text-slate-600">৳1,050</td></tr>
                    <tr><td class="px-3 py-2 font-semibold text-slate-700">Total Fine</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">unpaid/partial/overdue due-গুলোর মধ্যে জমে থাকা মোট ফাইন</td><td class="px-3 py-2 text-right text-slate-600">৳50</td></tr>
                    <tr><td class="px-3 py-2 font-semibold text-slate-700">Total Deposited</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">ভেরিফাইড পেমেন্টের সমষ্টি + সক্রিয় শেয়ারের মূল্য</td><td class="px-3 py-2 text-right text-slate-600">৳0 (এখনো কোনো পেমেন্ট verify হয়নি)</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- 17. APPENDIX --}}
    {{-- ============================================================ --}}
    <div id="appendix" class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[12px] font-bold text-white">১৭</span>
            <h2 class="text-sm font-bold text-slate-800">অ্যাপেন্ডিক্স — সব মডিউলের জার্নাল একনজরে</h2>
        </div>

        <p class="mb-2  text-xs 2xl:text-sm leading-6 text-slate-600">দ্রুত রেফারেন্সের জন্য সব মডিউলের ডেবিট/ক্রেডিট এক টেবিলে:</p>

        <div class="overflow-x-auto rounded-md border border-slate-200">
            <table class="w-full  text-xs 2xl:text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">মডিউল / ইভেন্ট</th>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">Debit</th>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">Credit</th>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">Approval লাগে?</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                    $appendixRows = [
                        ['সাবস্ক্রিপশন Due তৈরি','Accounts Receivable','Subscription Income',false],
                        ['সাবস্ক্রিপশন Late Fine','Accounts Receivable','Fine Income',false],
                        ['সাবস্ক্রিপশন Payment Verify','Cash/Bank','Accounts Receivable',true],
                        ['মেম্বার চার্জ তৈরি','Accounts Receivable','Charge/Applicable Income',true],
                        ['মেম্বার চার্জ পরিশোধ (Pay)','Cash/Bank','Accounts Receivable',false],
                        ['লোন Request/ডিসবার্স','Member Loan Receivable','Cash/Bank',true],
                        ['লোন রিপেমেন্ট','Cash/Bank','Member Loan Receivable (+Interest Income)',false],
                        ['বিনিয়োগ (Investment) তৈরি','Investments','Cash/Bank',false],
                        ['বিনিয়োগ রিটার্ন প্রাপ্তি','Cash/Bank','Investment Income (বা Investments, principal হলে)',false],
                        ['শেয়ার ক্রয় (Purchase Request)','Cash/Bank','Member Share Capital',true],
                        ['মেম্বার এক্সিট সেটেলমেন্ট','Member Share Capital','Cash/Bank',true],
                        ['সম্পদ ক্রয় (Asset Create)','Fixed Assets','Cash/Bank',true],
                        ['অ্যাসেট Depreciation','Depreciation Expense','Fixed Asset / Accumulated Depreciation',false],
                        ['সম্পদ বিক্রয়/অপসারণ (Sell/Dispose)','Cash/Bank (+Loss, হলে)','Fixed Asset (+Gain, হলে)',false],
                        ['টেলার — Receive','Cash','নির্বাচিত Counter Account',false],
                        ['টেলার — Payment','নির্বাচিত Counter Account','Cash',false],
                        ['সরাসরি Income Entry','Cash/Bank','নির্দিষ্ট Income অ্যাকাউন্ট',true],
                        ['সরাসরি Expense Entry','নির্দিষ্ট Expense অ্যাকাউন্ট','Cash/Bank',true],
                        ['ম্যানুয়াল জার্নাল এন্ট্রি','—','—',true],
                        ['নতুন Account তৈরি / Update / Delete','—','—',true],
                    ];
                    @endphp
                    @foreach($appendixRows as [$event, $dr, $cr, $needsApproval])
                    <tr>
                        <td class="px-3 py-2 text-slate-700">{{ $event }}</td>
                        <td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">{{ $dr }}</td>
                        <td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">{{ $cr }}</td>
                        <td class="px-3 py-2 text-xs 2xl:text-sm">
                            @if($needsApproval)
                                <span class="font-semibold text-amber-600">হ্যাঁ 🔒</span>
                            @else
                                <span class="text-slate-500">না</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3 rounded-md border border-amber-200 bg-amber-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-amber-800">
            <p class="mb-1 font-bold">🔒 = Approval Workflow-এর আওতাধীন</p>
            যেসব সারিতে "হ্যাঁ 🔒" লেখা, সেগুলো সেভ করার সাথে সাথে জার্নালে Post হয় না — আগে একজন (বা একাধিক ধাপে)
            নির্ধারিত Approver-কে Approve করতে হয়, তারপর জার্নাল Post হয়। বিস্তারিত নিয়ম ও পুরো তালিকার জন্য দেখুন
            সেকশন ৫ — Approval Workflow।
        </div>
        <div class="mt-3 rounded-md border border-indigo-200 bg-indigo-50/50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-indigo-700">
            📌 এই টেবিলটা শুধু দিক-নির্দেশনার জন্য — প্রতিটার সঠিক ধাপ, শর্ত ও বাস্তব উদাহরণ উপরের নিজস্ব সেকশনে
            বিস্তারিত দেওয়া আছে।
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- 18. REPORTS --}}
    {{-- ============================================================ --}}
    <div id="reports" class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[12px] font-bold text-white">১৮</span>
            <h2 class="text-sm font-bold text-slate-800">রিপোর্টসমূহ</h2>
        </div>

        <div class="overflow-x-auto rounded-md border border-slate-200">
            <table class="w-full  text-xs 2xl:text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">রিপোর্ট</th>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">কী দেখায়</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr><td class="px-3 py-2 font-semibold text-slate-700">General Ledger</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">একটি নির্দিষ্ট অ্যাকাউন্টের সব লেনদেন, তারিখ অনুযায়ী, চলমান ব্যালেন্সসহ।</td></tr>
                    <tr><td class="px-3 py-2 font-semibold text-slate-700">Trial Balance</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">নির্দিষ্ট সময়ে সব অ্যাকাউন্টের Debit ও Credit ব্যালেন্স তালিকা — মোট মিলতেই হবে।</td></tr>
                    <tr><td class="px-3 py-2 font-semibold text-slate-700">Balance Sheet</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">একটি নির্দিষ্ট তারিখে Assets = Liabilities + Equity অবস্থা।</td></tr>
                    <tr><td class="px-3 py-2 font-semibold text-slate-700">Profit &amp; Loss (আয়-ব্যয়)</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">নির্দিষ্ট সময়ের মোট আয় বনাম মোট ব্যয় এবং নিট উদ্বৃত্ত/ঘাটতি।</td></tr>
                    <tr><td class="px-3 py-2 font-semibold text-slate-700">Journal Entries</td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">সব ধরনের (স্বয়ংক্রিয় + ম্যানুয়াল) জার্নালের তালিকা, ফিল্টার ও সার্চসহ।</td></tr>
                </tbody>
            </table>
        </div>
        <p class="mt-3  text-xs 2xl:text-sm leading-6 text-slate-600">প্রায় প্রতিটি রিপোর্ট থেকে PDF/Excel এক্সপোর্ট এবং প্রিন্ট করার সুবিধা আছে।</p>
    </div>

    {{-- ============================================================ --}}
    {{-- 19. RECEIPTS --}}
    {{-- ============================================================ --}}
    <div id="receipts" class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[12px] font-bold text-white">১৯</span>
            <h2 class="text-sm font-bold text-slate-800">রসিদ / প্রিন্ট (Receipts)</h2>
        </div>

        <h3 class="mb-2  text-xs 2xl:text-sm font-bold text-slate-800">ক) সদস্যের পেমেন্ট রসিদ (Member Payment Receipt)</h3>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-md border border-slate-200 bg-white p-3">
                <span class="mb-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[11px] font-bold text-white">১</span>
                <p class=" text-xs 2xl:text-sm text-slate-600">সদস্য <code class="rounded bg-slate-100 px-1 py-0.5 font-mono text-[10px] text-indigo-700">Member → Monthly Dues → Pay</code>-তে গিয়ে পেমেন্ট সাবমিট করেন।</p>
            </div>
            <div class="rounded-md border border-slate-200 bg-white p-3">
                <span class="mb-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[11px] font-bold text-white">২</span>
                <p class=" text-xs 2xl:text-sm text-slate-600">সফল হলে সাথে সাথে একটি <b>Payment Receipt</b> পপ-আপ দেখানো হয়।</p>
            </div>
            <div class="rounded-md border border-slate-200 bg-white p-3">
                <span class="mb-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[11px] font-bold text-white">৩</span>
                <p class=" text-xs 2xl:text-sm text-slate-600">রসিদে থাকে: Receipt No, তারিখ, সদস্যের নাম/কোড, মাস, পেমেন্ট মেথড, রেফারেন্স, স্ট্যাটাস ও পরিমাণ।</p>
            </div>
            <div class="rounded-md border border-slate-200 bg-white p-3">
                <span class="mb-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[11px] font-bold text-white">৪</span>
                <p class=" text-xs 2xl:text-sm text-slate-600"><b>Print Receipt</b> বাটনে ক্লিক করলে প্রিন্ট-উপযোগী ফরম্যাটে নতুন ট্যাবে খুলবে।</p>
            </div>
        </div>
        <div class="mt-3 rounded-md border border-amber-200 bg-amber-50 px-4 py-3  text-xs 2xl:text-sm leading-5 text-amber-800">
            <p class="mb-1 font-bold">মনে রাখবেন</p>
            এই রসিদটি শুধু "পেমেন্ট সাবমিট হয়েছে" তার প্রমাণ — চূড়ান্ত হিসাব-ভুক্তি (Journal Posting) হয় অ্যাডমিন ভেরিফাই করার পর।
        </div>

        <h3 class="mb-2 mt-4  text-xs 2xl:text-sm font-bold text-slate-800">খ) অ্যাডমিন জার্নাল ভাউচার (Journal Voucher Print)</h3>
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-md border border-slate-200 bg-white p-3">
                <span class="mb-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[11px] font-bold text-white">১</span>
                <p class=" text-xs 2xl:text-sm text-slate-600"><code class="rounded bg-slate-100 px-1 py-0.5 font-mono text-[10px] text-indigo-700">Finance → Journal Entries</code>-এ গিয়ে যেকোনো এন্ট্রির <b>View</b>-তে ক্লিক করুন।</p>
            </div>
            <div class="rounded-md border border-slate-200 bg-white p-3">
                <span class="mb-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[11px] font-bold text-white">২</span>
                <p class=" text-xs 2xl:text-sm text-slate-600">"Journal Details" মডালে রসিদ আকারে সম্পূর্ণ ভাউচার দেখা যাবে।</p>
            </div>
            <div class="rounded-md border border-slate-200 bg-white p-3">
                <span class="mb-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[11px] font-bold text-white">৩</span>
                <p class=" text-xs 2xl:text-sm text-slate-600"><b>Print Receipt</b> বাটনে ক্লিক করলে প্রিন্ট প্রিভিউ খুলবে — যেকোনো প্রিন্টার/PDF-এ সেভ করা যাবে।</p>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- 20. PERMISSIONS --}}
    {{-- ============================================================ --}}
    <div id="permissions" class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[12px] font-bold text-white">২০</span>
            <h2 class="text-sm font-bold text-slate-800">পারমিশন ও রোল</h2>
        </div>

        <div class="overflow-x-auto rounded-md border border-slate-200">
            <table class="w-full  text-xs 2xl:text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">পারমিশন</th>
                        <th class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-slate-500">কী করতে পারবে</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr><td class="px-3 py-2 text-xs 2xl:text-sm"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">Finance.view</code></td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">জার্নাল, রিপোর্ট, লেজার দেখতে পারবে।</td></tr>
                    <tr><td class="px-3 py-2 text-xs 2xl:text-sm"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">Finance.create</code></td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">ম্যানুয়াল জার্নাল পোস্ট করতে পারবে, নতুন আয়/ব্যয়/পেমেন্ট এন্ট্রি করতে পারবে।</td></tr>
                    <tr><td class="px-3 py-2 text-xs 2xl:text-sm"><code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">Finance.update</code></td><td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">জার্নাল Reverse করতে পারবে, পেমেন্ট Verify/Reject করতে পারবে।</td></tr>
                </tbody>
            </table>
        </div>
        <p class="mt-3  text-xs 2xl:text-sm leading-6 text-slate-600">
            রোল ও পারমিশন <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">Settings → Roles</code>
            থেকে নিয়ন্ত্রণ করা যায়। সাধারণ ট্রেজারার/হিসাবরক্ষক ব্যবহারকারীদের অন্তত
            <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">Finance.view</code>
            ও <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">Finance.create</code>
            দেওয়া উচিত।
        </p>
    </div>

    {{-- ============================================================ --}}
    {{-- 21. FAQ --}}
    {{-- ============================================================ --}}
    <div id="faq" class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[12px] font-bold text-white">২১</span>
            <h2 class="text-sm font-bold text-slate-800">সাধারণ সমস্যা ও সমাধান</h2>
        </div>

        <div class="space-y-4">
            <div>
                <h4 class="mb-1  text-xs 2xl:text-sm font-bold text-indigo-600">জার্নাল পোস্ট হচ্ছে না, "Not Balanced" দেখাচ্ছে</h4>
                <p class=" text-xs 2xl:text-sm leading-6 text-slate-600">প্রতিটি লাইনের Debit ও Credit যোগফল আলাদাভাবে মিলিয়ে দেখুন — টোটাল Debit অবশ্যই টোটাল Credit-এর সমান হতে হবে। উদাহরণস্বরূপ, ৩ লাইনের একটি ম্যানুয়াল জার্নালে যদি Debit-এ ২,১৫,০০০ এবং Credit-এ ২,১০,০০০ থাকে, তাহলে ৫,০০০ টাকার পার্থক্য খুঁজে বের করে ঠিক করতে হবে।</p>
            </div>
            <div>
                <h4 class="mb-1  text-xs 2xl:text-sm font-bold text-indigo-600">ভুল এন্ট্রি হয়ে গেছে, এখন কী করবো?</h4>
                <p class=" text-xs 2xl:text-sm leading-6 text-slate-600">এন্ট্রি এডিট/ডিলিট করা যায় না। <b>Reverse Journal</b> ব্যবহার করে এর বিপরীত এন্ট্রি পোস্ট করুন — এটি হিসাবরক্ষণের নিরাপদ পদ্ধতি। যেমন, ভুলে ৳15,000-এর বদলে ৳51,000 অফিস খরচ এন্ট্রি হয়ে গেলে, প্রথমে পুরো এন্ট্রিটা Reverse করুন, তারপর সঠিক পরিমাণে নতুন এন্ট্রি করুন।</p>
            </div>
            <div>
                <h4 class="mb-1  text-xs 2xl:text-sm font-bold text-indigo-600">সদস্যের পেমেন্ট জার্নালে দেখা যাচ্ছে না</h4>
                <p class=" text-xs 2xl:text-sm leading-6 text-slate-600">পেমেন্ট প্রথমে "Pending" অবস্থায় থাকে। <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">Finance → Subscription Payments</code>-এ গিয়ে অ্যাডমিন কর্তৃক Verify করার পরই এটি জার্নালে Posted হিসেবে দেখাবে (দেখুন সেকশন ৬, ধাপ ৩-৪)।</p>
            </div>
            <div>
                <h4 class="mb-1  text-xs 2xl:text-sm font-bold text-indigo-600">টেবিলে হরাইজন্টাল স্ক্রল আসছে কেন, আগে হতো?</h4>
                <p class=" text-xs 2xl:text-sm leading-6 text-slate-600">জার্নাল লিস্টের টেবিল থেকে Type ও Source কলাম সরিয়ে Description-এর নিচে ছোট আকারে দেখানো হচ্ছে, এবং টেবিলটি এখন ফিক্সড-লেআউটে সেট করা — ফলে এখন থেকে টেবিলে অতিরিক্ত স্ক্রলবার আসবে না। সম্পূর্ণ তথ্য দেখতে View/রসিদে ক্লিক করুন।</p>
            </div>
            <div>
                <h4 class="mb-1  text-xs 2xl:text-sm font-bold text-indigo-600">রিপোর্টে Total মিলছে না</h4>
                <p class=" text-xs 2xl:text-sm leading-6 text-slate-600">Trial Balance রিপোর্টে গিয়ে দেখুন কোনো অ্যাকাউন্টে অস্বাভাবিক ব্যালেন্স আছে কিনা। সাধারণত কোনো Reversal সঠিকভাবে না হলে বা কোনো এন্ট্রি ভুল অ্যাকাউন্টে পোস্ট হলে এমন হতে পারে — এক্ষেত্রে সংশ্লিষ্ট জার্নাল ভাউচার প্রিন্ট করে যাচাই করুন।</p>
            </div>
            <div>
                <h4 class="mb-1  text-xs 2xl:text-sm font-bold text-indigo-600">Due তৈরি হওয়ার সাথে সাথেই কেন Income বেড়ে যায়, টাকা তো এখনো আসেনি?</h4>
                <p class=" text-xs 2xl:text-sm leading-6 text-slate-600">এই সিস্টেম Accrual Basis-এ চলে (দেখুন সেকশন ২) — অর্থাৎ পাওনা তৈরি হওয়ার দিনই আয় ধরা হয়, টাকা হাতে আসার দিন নয়। এটা হিসাবশাস্ত্রের স্বীকৃত ও প্রচলিত নিয়ম, ভুল নয়।</p>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <p class="pt-2 text-center text-[11px] text-slate-400">
        Dreamers Association — Internal Admin Manual (Combined &amp; Detailed Edition) · SubscriptionService, AccountingService এবং সংশ্লিষ্ট মডিউল সার্ভিসসমূহের ভিত্তিতে প্রস্তুতকৃত · অভ্যন্তরীণ ব্যবহারের জন্য
    </p>

</div>
@endsection