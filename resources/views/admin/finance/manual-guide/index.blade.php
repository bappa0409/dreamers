@extends('layouts.admin')

@section('title','Accounts Guide')
@section('page_title','Accounts Guide')

@section('content')
<div class="space-y-4">

    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-book"></i>
            </div>

            <div>
                <h1 class="text-base font-bold text-slate-800">Accounts (Ledger) — কোন কাজে হিট হয়</h1>
                <p class=" text-xs 2xl:text-sm text-slate-500">
                    সিস্টেমের কোন action ledger-এ entry তৈরি করে, এবং সেটা কোথায় গিয়ে দেখা যাবে — তার রেফারেন্স ম্যাপ।
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('admin.finance.manual-detail') }}"
                class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-2 text-xs 2xl:text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
            >
                <i class="bi bi-journal-text text-[11px]"></i>
                Accounts Detail
            </a>
        </div>
    </div>

    <div class="rounded-md border border-indigo-200 bg-indigo-50/50 px-4 py-3 text-xs 2xl:text-sm leading-5 text-indigo-700">
        <i class="bi bi-info-circle mr-1"></i>
        সিস্টেমে যত ধরনের accounting entry তৈরি হয়, সবগুলো একই common নিয়মে check হয়ে ledger-এ post হয় —
        প্রতিটা entry-তে Debit ও Credit-এর total সমান কিনা যাচাই করা হয়, এবং একই action দুইবার ভুলবশত জমা হয়ে
        entry duplicate হয়ে যাওয়া আটকানো হয়।
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <div class="rounded-md border-l-4 border-l-emerald-500 border border-slate-200 bg-white p-4">
            <p class="text-sm font-semibold text-slate-800">Posted (workflow না থাকলে)</p>
            <p class="mt-1 text-xs 2xl:text-sm text-slate-500">যেকোনো wired module/action (Member, Loan, Income, Expense, Account, Manual Journal — সবগুলোই) তৈরি হওয়ার সাথে সাথে approval request auto-approve হয়ে যায়, আর একই মুহূর্তেই সরাসরি ledger-এ posted/effective হয়ে যায়।</p>
        </div>
        <div class="rounded-md border-l-4 border-l-amber-500 border border-slate-200 bg-white p-4">
            <p class="text-sm font-semibold text-slate-800">Pending (workflow থাকলে)</p>
            <p class="mt-1 text-xs 2xl:text-sm text-slate-500">Active workflow বসানো থাকলে entry pending অবস্থায় থাকে যতক্ষণ না approve হয় (Manual Journal-এর ক্ষেত্রে ততক্ষণ transaction draft) — approve হলে তবেই একই posting logic চলে।</p>
        </div>
    </div>

    <div class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <h2 class="text-sm font-bold text-slate-800">Workflow → Accounting Hit ম্যাপ</h2>
    </div>

    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white">
        <table class="w-full text-sm">
            <thead class="border-b border-slate-200 bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold uppercase tracking-wide text-slate-500">Sidebar Module</th>
                    <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold uppercase tracking-wide text-slate-500">কী করলে hit হয়</th>
                    <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold uppercase tracking-wide text-slate-500">Type</th>
                    <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold uppercase tracking-wide text-slate-500">Debit / Credit</th>
                    <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold uppercase tracking-wide text-slate-500">কোথায় দেখবেন</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php
                $rows = [
                    ['Membership → Members','নতুন member approve করলে initial share auto-purchase হয়','member_initial_share','Dr Cash/Bank/Receivable · Cr Member Equity','Members → profile / Finance → Share Purchases'],
                    ['Finance → Share Purchases','Member নতুন share কিনলে ও verify হলে','member_share_purchase','Dr Cash/Bank · Cr Member Equity','Finance → Share Purchases'],
                    ['Finance → Subscription Payments','মাসিক subscription due generate হলে','subscription_due','Dr Receivable · Cr Subscription Income','Finance → Subscription Payments'],
                    ['','Subscription payment collect হলে','subscription_payment','Dr Cash/Bank/Online · Cr Receivable','Finance → Subscription Payments'],
                    ['','Late payment fine লাগলে','subscription_fine','Dr Receivable · Cr Fine Income','Finance → Subscription Payments'],
                    ['Finance & Assets → Charges','Member-কে charge (fee) ধার্য করলে','member_charge','Dr Receivable · Cr Charge Income','Charges'],
                    ['','Charge payment নিলে','charge_payment','Dr Cash/Bank · Cr Receivable','Charges'],
                    ['','Charge waive/reverse করলে','member_charge_waiver / *_reversal','Reverse entry','Charges → ওই charge'],
                    ['Finance & Assets → Loans','Loan disburse করলে','loan_disbursement','Dr Loan Receivable · Cr Cash/Bank','Loans'],
                    ['','Repayment (কিস্তি) নিলে','loan_repayment','Dr Cash/Bank · Cr Loan Receivable + Interest Income','Loans → ওই loan'],
                    ['Finance & Assets → Investments','নতুন investment করলে','investment','Dr Investment · Cr Cash/Bank','Investments'],
                    ['','Principal ফেরত এলে','investment_principal_return','Dr Cash/Bank · Cr Investment','Investments → ওই investment'],
                    ['','Investment income/profit এলে','investment_income','Dr Cash/Bank · Cr Investment Income','Investments → ওই investment'],
                    ['Finance → Assets','Asset কিনলে','asset_purchase','Dr Asset · Cr Cash/Bank/Payable','Assets'],
                    ['','Asset বিক্রি করলে','asset_sale','Dr Cash/Bank · Cr Asset (+gain/loss)','Assets → ওই asset'],
                    ['','Asset disposal (scrap) করলে','asset_disposal','Write-off entry','Assets → ওই asset'],
                    ['','Depreciation run করলে','asset_depreciation','Dr Depreciation Expense · Cr Accumulated Depreciation','Assets → Depreciation ট্যাব'],
                    ['Finance & Assets → Land Management','জমি কিনলে','land_purchase','Dr Land · Cr Cash/Bank/Payable','Land Management'],
                    ['','জমি বিক্রি করলে','land_sale','Dr Cash/Bank · Cr Land (+gain/loss)','Land Management → ওই land'],
                    ['Finance → Incomes','Manual income entry করলে','income','Dr Cash/Bank · Cr Income','Incomes'],
                    ['Finance → Expenses','Expense entry করলে','expense','Dr Expense · Cr Cash/Bank/Payable','Expenses'],
                    ['Finance & Assets → Welfare Fund','Welfare assistance দিলে','welfare_assistance','Dr Welfare Expense · Cr Cash/Bank','Welfare Fund'],
                    ['Governance → Meetings','Meeting-এ খরচ লগ করলে','meeting_expense','Dr Expense · Cr Cash/Bank','Meetings → ওই meeting'],
                    ['Operations → Tours','Tour-এর খরচ লগ করলে','tour_expense','Dr Expense · Cr Cash/Bank','Tours → ওই tour'],
                    ['Membership → Member Exit','Exit settlement approve/pay করলে','member_exit_settlement','Dr Member Equity (share refund) · Cr Cash/Bank','Member Exit → ওই exit'],
                    ['Teller (Cash Counter)','Teller cash receive করলে','teller_receive','Dr Cash · Cr counter account','Finance Dashboard-এর Teller widget'],
                    ['','Teller cash payment করলে','teller_payment','Dr counter account · Cr Cash','Finance Dashboard-এর Teller widget'],
                    ['Finance → Journal Entries','Admin নিজে journal entry বানালে','manual_journal','Draft → Approval → Posted','Journal Entries'],
                ];
                @endphp

                @foreach($rows as [$module, $action, $type, $entry, $where])
                <tr class="align-top">
                    <td class="px-4 py-2.5 text-xs 2xl:text-sm font-semibold text-slate-700">{{ $module }}</td>
                    <td class="px-4 py-2.5 text-xs 2xl:text-sm text-slate-600">{{ $action }}</td>
                    <td class="px-4 py-2.5 text-xs 2xl:text-sm">
                        <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">{{ $type }}</code>
                    </td>
                    <td class="px-4 py-2.5 text-xs 2xl:text-sm text-slate-600">{{ $entry }}</td>
                    <td class="px-4 py-2.5 text-xs 2xl:text-sm text-slate-500">{{ $where }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-xs 2xl:text-sm leading-5 text-amber-800">
        <i class="bi bi-arrow-repeat mr-1"></i>
        <strong>Reversal:</strong> উপরের প্রায় প্রতিটা module-এ reverse/cancel action আছে (<code class="rounded bg-white px-1.5 py-0.5 font-mono text-[11px] text-amber-700">*_reversal</code>) —
        original entry-র উল্টো debit/credit দিয়ে নতুন transaction পোস্ট হয়, original টা delete হয় না (audit trail থাকে)।
    </div>

    <div class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <h2 class="text-sm font-bold text-slate-800">Approval Workflow না বসালে কী হয় — সব module/action</h2>
        <p class="mt-1 text-xs 2xl:text-sm text-slate-500">
            এই লিস্টের বাইরে কোনো module/action-এর জন্য Approval Workflow Builder থেকে workflow বানানোই যায় না —
            এই কয়টাই একমাত্র module/action যেগুলোর জন্য সিস্টেম আসলে approval চেক করে।
            যেকোনো একটার জন্য active workflow (কমপক্ষে ১টা approver step সহ) না থাকলে — request block হয় না, সাথে সাথে
            <strong>auto-approve (self-approved)</strong> হয়ে সরাসরি posted/created/activated হয়ে যায়, কারো approve করার জন্য wait করে না।
            Workflow থাকলে সেটা approval-এর জন্য pending থাকে, approve হলে তবেই একই posting logic চলে।
        </p>
    </div>

    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white">
        <table class="w-full text-sm">
            <thead class="border-b border-slate-200 bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold uppercase tracking-wide text-slate-500">Module</th>
                    <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold uppercase tracking-wide text-slate-500">Action</th>
                    <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold uppercase tracking-wide text-slate-500">Workflow না থাকলে (auto-approve)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php
                $workflowRows = [
                    ['Member','create','নতুন member সাথে সাথেই approve হয়ে যায়, initial share auto-purchase হয়'],
                    ['Loan','request','Loan সাথে সাথে approve হয়ে disbursement posting চলে যায়'],
                    ['Welfare','request','Welfare request সাথে সাথে approve হয়ে assistance posting চলে যায়'],
                    ['MemberExit','request','Exit request সাথে সাথে approve হয়ে settlement posting চলে যায়'],
                    ['MemberShare','request','Share purchase সাথে সাথে approve হয়ে verify হয়ে যায়'],
                    ['Tour','approve','Tour সাথে সাথে approve হয়ে যায়'],
                    ['Account','create, update, delete','Account create/update/delete সাথে সাথেই effective হয়ে যায়'],
                    ['Income','create','Income entry সাথে সাথেই posted হয়ে যায়'],
                    ['Expense','create','Expense entry সাথে সাথেই posted হয়ে যায়'],
                    ['SubscriptionPayment','verify','Subscription payment সাথে সাথে verify হয়ে যায়'],
                    ['Charge','create','Member charge সাথে সাথেই posted হয়ে যায়'],
                    ['Asset','create','Asset purchase সাথে সাথেই posted হয়ে যায়'],
                    ['JournalEntry','create','Manual journal draft না থেকে সাথে সাথেই posted হয়ে যায়'],
                ];
                @endphp

                @foreach($workflowRows as [$module, $action, $behavior])
                <tr class="align-top">
                    <td class="px-4 py-2.5 text-xs 2xl:text-sm font-semibold text-slate-700">{{ $module }}</td>
                    <td class="px-4 py-2.5 text-xs 2xl:text-sm">
                        <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">{{ $action }}</code>
                    </td>
                    <td class="px-4 py-2.5 text-xs 2xl:text-sm text-slate-600">{{ $behavior }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="rounded-md border border-slate-200 bg-slate-50 px-4 py-3 text-xs 2xl:text-sm leading-5 text-slate-600">
        <i class="bi bi-gear mr-1"></i>
        Compulsory approval-এর মধ্য দিয়ে নিতে চাইলে <strong>Approval → Workflow Builder</strong>-এ গিয়ে ওই module/action-এর জন্য
        একটা active workflow (কমপক্ষে ১টা approver step সহ) বসাতে হবে।
    </div>

    <div class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <h2 class="text-sm font-bold text-slate-800">সব entry একসাথে কোথায় দেখবেন</h2>
        <ul class="mt-2 space-y-1.5 text-xs 2xl:text-sm text-slate-600">
            <li><i class="bi bi-journal-text mr-1 text-indigo-500"></i> <strong>Finance → Journal Entries</strong> — সব posted/draft transaction, <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700">source_module</code> দিয়ে filter করা যায়</li>
            <li><i class="bi bi-journal-bookmark mr-1 text-indigo-500"></i> <strong>Finance → General Ledger</strong> — account-wise সব entry</li>
            <li><i class="bi bi-bar-chart-steps mr-1 text-indigo-500"></i> <strong>Finance → Trial Balance / Balance Sheet / Profit &amp; Loss</strong> — সারাংশ রিপোর্ট</li>
        </ul>
    </div>

</div>
@endsection