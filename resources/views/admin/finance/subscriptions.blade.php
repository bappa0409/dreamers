@extends('layouts.admin')

@section('title','Monthly Subscriptions')
@section('page_title','Monthly Subscriptions')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Monthly Subscriptions</h1>
                <p class="text-sm text-slate-500">Manage member subscriptions, monthly dues and payments.</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" onclick="openPlanModal()" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                <i class="bi bi-gear"></i>
                Plans
            </button>
            <button type="button" onclick="openAssignModal()" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                <i class="bi bi-person-plus"></i>
                Assign Subscription
            </button>
            <button type="button" onclick="confirmBulkGenerate()" class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                <i class="bi bi-calendar-plus"></i>
                Generate Dues
            </button>
        </div>
    </div>

    <div id="pageAlert" class="hidden rounded-md border px-4 py-3 text-sm"></div>

    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Total Due</p>
                    <p id="summaryDue" class="mt-1 text-xl font-bold text-slate-800">৳0.00</p>
                </div>
                <i class="bi bi-receipt text-xl text-slate-300"></i>
            </div>
        </div>
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Collected</p>
                    <p id="summaryPaid" class="mt-1 text-xl font-bold text-slate-800">৳0.00</p>
                </div>
                <i class="bi bi-check-circle text-xl text-slate-300"></i>
            </div>
        </div>
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Outstanding</p>
                    <p id="summaryOutstanding" class="mt-1 text-xl font-bold text-slate-800">৳0.00</p>
                </div>
                <i class="bi bi-hourglass-split text-xl text-slate-300"></i>
            </div>
        </div>
    </div>

    <div class="rounded-md border border-slate-200 bg-white">
        <div class="flex flex-col gap-2 border-b border-slate-200 p-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap gap-2">
                <input id="searchInput" type="text" placeholder="Search member..." class="w-56 rounded-md border border-slate-300 px-3 py-2 text-xs outline-none focus:border-indigo-400">
                <select id="monthFilter" class="rounded-md border border-slate-300 px-3 py-2 text-xs outline-none"></select>
                <select id="yearFilter" class="rounded-md border border-slate-300 px-3 py-2 text-xs outline-none"></select>
                <select id="statusFilter" class="rounded-md border border-slate-300 px-3 py-2 text-xs outline-none">
                    <option value="">All Subscriptions</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="loadPage(1)" class="rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                    <i class="bi bi-search"></i>
                    Filter
                </button>
                <button type="button" onclick="loadPayments()" class="rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                    <i class="bi bi-credit-card"></i>
                    Pending Payments
                </button>
            </div>
        </div>

        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Member</th>
                        <th class="px-4 py-3">Plan</th>
                        <th class="px-4 py-3 text-right">Monthly Amount</th>
                        <th class="px-4 py-3">Selected Month</th>
                        <th class="px-4 py-3 text-right">Due</th>
                        <th class="px-4 py-3 text-right">Paid</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="subscriptionBody">
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-slate-400">Loading subscriptions...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="pagination" class="flex items-center justify-between border-t border-slate-200 px-4 py-3 text-xs text-slate-500"></div>
    </div>
</div>

{{-- Assign Subscription --}}
<div id="assignModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3">
    <div class="app-modal-panel w-full max-w-lg overflow-hidden rounded-md bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h2 class="font-bold text-slate-800">Assign Subscription</h2>
            <button type="button" onclick="closeModal('assignModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
        </div>
        <form id="assignForm" novalidate data-js-validation="1">
            <div class="space-y-4 p-5">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600">Member</label>
                    <select id="assignMember" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" required></select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600">Subscription Plan</label>
                    <select id="assignPlan" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" required></select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600">Start Date</label>
                    <input id="assignStartDate" type="date" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" required>
                </div>
                <div id="assignError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-600"></div>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="closeModal('assignModal')" class="rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600">Cancel</button>
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">Assign</button>
            </div>
        </form>
    </div>
</div>

{{-- Plans --}}
<div id="planModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3">
    <div class="app-modal-panel w-full max-w-3xl overflow-hidden rounded-md bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="font-bold text-slate-800">Subscription Plans</h2>
                <p class="text-xs text-slate-500">Configure monthly subscription amounts.</p>
            </div>
            <button type="button" onclick="closeModal('planModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="grid gap-4 p-5 md:grid-cols-[280px_1fr]">
            <form id="planForm" class="space-y-3" novalidate data-js-validation="1">
                <input id="planId" type="hidden">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600">Plan Name</label>
                    <input id="planName" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600">Monthly Amount</label>
                    <input id="planAmount" type="number" min="0.01" step="0.01" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600">Due Day</label>
                    <input id="planDueDay" type="number" min="1" max="31" value="10" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" required>
                </div>
                <label class="flex items-center gap-2 text-xs text-slate-600">
                    <input id="planDefault" type="checkbox">
                    Default plan
                </label>
                <label class="flex items-center gap-2 text-xs text-slate-600">
                    <input id="planActive" type="checkbox" checked>
                    Active
                </label>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600">Description</label>
                    <textarea id="planDescription" rows="2" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"></textarea>
                </div>
                <div id="planError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-600"></div>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">Save Plan</button>
                    <button type="button" onclick="resetPlanForm()" class="rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600">Clear</button>
                </div>
            </form>
            <div class="overflow-hidden rounded-md border border-slate-200">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs">Plan</th>
                            <th class="px-3 py-2 text-right text-xs">Amount</th>
                            <th class="px-3 py-2 text-center text-xs">Due</th>
                            <th class="px-3 py-2 text-right text-xs"></th>
                        </tr>
                    </thead>
                    <tbody id="planBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Payments --}}
<div id="paymentListModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3">
    <div class="app-modal-panel w-full max-w-5xl overflow-hidden rounded-md bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="font-bold text-slate-800">Pending Payments</h2>
                <p class="text-xs text-slate-500">Verify member submitted subscription payments.</p>
            </div>
            <button type="button" onclick="closeModal('paymentListModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="max-h-[65vh] overflow-auto">
            <table class="w-full min-w-[800px] text-sm">
                <thead class="sticky top-0 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs">Payment</th>
                        <th class="px-4 py-3 text-left text-xs">Member</th>
                        <th class="px-4 py-3 text-left text-xs">Period</th>
                        <th class="px-4 py-3 text-right text-xs">Amount</th>
                        <th class="px-4 py-3 text-left text-xs">Method</th>
                        <th class="px-4 py-3 text-left text-xs">Reference</th>
                        <th class="px-4 py-3 text-right text-xs">Actions</th>
                    </tr>
                </thead>
                <tbody id="paymentBody"></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Reject --}}
<div id="rejectModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3">
    <div class="app-modal-panel w-full max-w-md overflow-hidden rounded-md bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h2 class="font-bold text-slate-800">Reject Payment</h2>
            <button type="button" onclick="closeModal('rejectModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
        </div>
        <form id="rejectForm" novalidate data-js-validation="1">
            <input id="rejectPaymentId" type="hidden">
            <div class="p-5">
                <label class="mb-1 block text-xs font-semibold text-slate-600">Reason</label>
                <textarea id="rejectReason" rows="4" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" required></textarea>
                <div id="rejectError" class="mt-3 hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-600"></div>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="closeModal('rejectModal')" class="rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600">Cancel</button>
                <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-xs font-semibold text-white">Reject Payment</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPage=1;
let plans=[];
let payments=[];

document.addEventListener('DOMContentLoaded',()=>{
    initializeFilters();
    loadPage(1);

    document.getElementById('searchInput').addEventListener('keydown',event=>{
        if(event.key==='Enter')loadPage(1);
    });

    document.getElementById('monthFilter').addEventListener('change',()=>loadPage(1));
    document.getElementById('yearFilter').addEventListener('change',()=>loadPage(1));
});

function initializeFilters(){
    const month=document.getElementById('monthFilter');
    const year=document.getElementById('yearFilter');
    const now=new Date();

    const months=[
        'January','February','March','April','May','June',
        'July','August','September','October','November','December'
    ];

    month.innerHTML=months.map((name,index)=>`
        <option value="${index+1}" ${index===now.getMonth()?'selected':''}>${name}</option>
    `).join('');

    let html='';
    for(let value=now.getFullYear()+1;value>=now.getFullYear()-5;value--){
        html+=`<option value="${value}" ${value===now.getFullYear()?'selected':''}>${value}</option>`;
    }
    year.innerHTML=html;
}

async function loadPage(page=1){
    currentPage=page;

    const params=new URLSearchParams({
        page,
        year:document.getElementById('yearFilter').value,
        month:document.getElementById('monthFilter').value,
    });

    const search=document.getElementById('searchInput').value.trim();
    const status=document.getElementById('statusFilter').value;

    if(search)params.set('search',search);
    if(status)params.set('status',status);

    document.getElementById('subscriptionBody').innerHTML=`
        <tr>
            <td colspan="8" class="px-4 py-10 text-center text-slate-400">
                <i class="bi bi-arrow-repeat animate-spin"></i>
                Loading subscriptions...
            </td>
        </tr>
    `;

    try{
        const [listResponse,summaryResponse]=await Promise.all([
            api(`/api/finance/subscriptions?${params}`),
            api(`/api/finance/subscriptions/summary?year=${params.get('year')}&month=${params.get('month')}`)
        ]);

        renderSubscriptions(listResponse.data?.data??[]);
        renderPagination(listResponse.data);
        renderSummary(summaryResponse.data??{});
    }catch(error){
        document.getElementById('subscriptionBody').innerHTML=`
            <tr>
                <td colspan="8" class="px-4 py-10 text-center text-red-500">
                    ${escapeHtml(extractError(error))}
                </td>
            </tr>
        `;
    }
}

function renderSummary(data){
    document.getElementById('summaryDue').textContent=money(data.total_due);
    document.getElementById('summaryPaid').textContent=money(data.total_paid);
    document.getElementById('summaryOutstanding').textContent=money(data.outstanding);
}

function renderSubscriptions(items){
    const body=document.getElementById('subscriptionBody');

    if(!items.length){
        body.innerHTML=`
            <tr>
                <td colspan="8" class="px-4 py-10 text-center text-sm text-slate-400">
                    No subscriptions found.
                </td>
            </tr>
        `;
        return;
    }

    const month=Number(document.getElementById('monthFilter').value);
    const year=document.getElementById('yearFilter').value;

    body.innerHTML=items.map(item=>{
        const due=item.dues?.[0]??null;
        const member=item.member??{};
        const user=member.user??{};

        return`
            <tr class="border-t border-slate-100 hover:bg-slate-50/50">
                <td class="px-4 py-3">
                    <div class="font-semibold text-slate-700">${escapeHtml(user.name??'—')}</div>
                    <div class="text-xs text-slate-400">${escapeHtml(member.member_code??'—')}</div>
                </td>
                <td class="px-4 py-3 text-slate-600">${escapeHtml(item.plan?.name??'—')}</td>
                <td class="px-4 py-3 text-right font-medium text-slate-700">${money(item.plan?.amount)}</td>
                <td class="px-4 py-3 text-slate-500">${monthName(month)} ${year}</td>
                <td class="px-4 py-3 text-right">${due?money(due.amount):'—'}</td>
                <td class="px-4 py-3 text-right">${due?money(due.paid_amount):'—'}</td>
                <td class="px-4 py-3">
                    ${due?dueBadge(due.status):'<span class="text-xs text-slate-400">Not Generated</span>'}
                </td>
                <td class="px-4 py-3">
                    <div class="flex justify-end gap-1">
                        ${!due&&item.is_active?`
                            <button type="button" onclick="generateSingleDue(${item.id})" class="inline-flex h-7 w-7 items-center justify-center rounded-md text-slate-500 hover:bg-indigo-50 hover:text-indigo-600" title="Generate Due">
                                <i class="bi bi-calendar-plus"></i>
                            </button>
                        `:''}
                        ${item.is_active?`
                            <button type="button" onclick="confirmDeactivate(${item.id},'${escapeJs(user.name??member.member_code??'Member')}')" class="inline-flex h-7 w-7 items-center justify-center rounded-md text-slate-500 hover:bg-red-50 hover:text-red-600" title="Deactivate">
                                <i class="bi bi-slash-circle"></i>
                            </button>
                        `:''}
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function renderPagination(meta){
    const box=document.getElementById('pagination');

    if(!meta||meta.last_page<=1){
        box.innerHTML=meta?.total?`<span>${meta.total} subscription(s)</span>`:'';
        return;
    }

    box.innerHTML=`
        <span>Page ${meta.current_page} of ${meta.last_page} · ${meta.total} total</span>
        <div class="flex gap-1">
            <button type="button" ${meta.current_page<=1?'disabled':''} onclick="loadPage(${meta.current_page-1})" class="rounded-md border border-slate-200 px-3 py-1.5 disabled:opacity-40">Prev</button>
            <button type="button" ${meta.current_page>=meta.last_page?'disabled':''} onclick="loadPage(${meta.current_page+1})" class="rounded-md border border-slate-200 px-3 py-1.5 disabled:opacity-40">Next</button>
        </div>
    `;
}

async function openAssignModal(){
    clearError('assignError');

    try{
        const [memberResponse,planResponse]=await Promise.all([
            api('/api/finance/subscriptions/members'),
            api('/api/finance/subscription-plans')
        ]);

        const members=memberResponse.data??[];
        plans=planResponse.data??[];

        document.getElementById('assignMember').innerHTML=`
            <option value="">Select member</option>
            ${members.map(item=>`
                <option value="${item.id}">
                    ${escapeHtml(item.member_code)} - ${escapeHtml(item.user?.name??'')}
                </option>
            `).join('')}
        `;

        document.getElementById('assignPlan').innerHTML=`
            <option value="">Select plan</option>
            ${plans.filter(item=>item.is_active).map(item=>`
                <option value="${item.id}" ${item.is_default?'selected':''}>
                    ${escapeHtml(item.name)} - ${money(item.amount)}
                </option>
            `).join('')}
        `;

        document.getElementById('assignStartDate').value=new Date().toISOString().slice(0,10);
        openModal('assignModal');
    }catch(error){
        showAlert('error',extractError(error));
    }
}

document.getElementById('assignForm').addEventListener('submit',async event=>{
    event.preventDefault();
    clearError('assignError');

    try{
        await api('/api/finance/subscriptions',{
            method:'POST',
            body:JSON.stringify({
                member_id:Number(document.getElementById('assignMember').value),
                subscription_plan_id:Number(document.getElementById('assignPlan').value),
                start_date:document.getElementById('assignStartDate').value
            })
        });

        closeModal('assignModal');
        showAlert('success','Subscription assigned successfully.');
        loadPage(1);
    }catch(error){
        showError('assignError',extractError(error));
    }
});

async function openPlanModal(){
    resetPlanForm();
    openModal('planModal');
    await loadPlans();
}

async function loadPlans(){
    try{
        const response=await api('/api/finance/subscription-plans');
        plans=response.data??[];
        renderPlans();
    }catch(error){
        showError('planError',extractError(error));
    }
}

function renderPlans(){
    const body=document.getElementById('planBody');

    if(!plans.length){
        body.innerHTML=`<tr><td colspan="4" class="px-3 py-8 text-center text-xs text-slate-400">No plans found.</td></tr>`;
        return;
    }

    body.innerHTML=plans.map(item=>`
        <tr class="border-t border-slate-100">
            <td class="px-3 py-2">
                <div class="font-medium text-slate-700">${escapeHtml(item.name)}</div>
                <div class="text-[10px] text-slate-400">
                    ${item.is_default?'Default · ':''}${item.is_active?'Active':'Inactive'}
                </div>
            </td>
            <td class="px-3 py-2 text-right">${money(item.amount)}</td>
            <td class="px-3 py-2 text-center">${item.due_day}</td>
            <td class="px-3 py-2">
                <div class="flex justify-end gap-1">
                    <button type="button" onclick="editPlan(${item.id})" class="inline-flex h-7 w-7 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" onclick="confirmDeletePlan(${item.id},'${escapeJs(item.name)}')" class="inline-flex h-7 w-7 items-center justify-center rounded-md text-slate-500 hover:bg-red-50 hover:text-red-600">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function editPlan(id){
    const plan=plans.find(item=>Number(item.id)===Number(id));
    if(!plan)return;

    document.getElementById('planId').value=plan.id;
    document.getElementById('planName').value=plan.name??'';
    document.getElementById('planAmount').value=plan.amount??'';
    document.getElementById('planDueDay').value=plan.due_day??10;
    document.getElementById('planDefault').checked=Boolean(plan.is_default);
    document.getElementById('planActive').checked=Boolean(plan.is_active);
    document.getElementById('planDescription').value=plan.description??'';
    clearError('planError');
}

function resetPlanForm(){
    document.getElementById('planForm').reset();
    document.getElementById('planId').value='';
    document.getElementById('planDueDay').value=10;
    document.getElementById('planActive').checked=true;
    clearError('planError');
}

document.getElementById('planForm').addEventListener('submit',async event=>{
    event.preventDefault();
    clearError('planError');

    const id=document.getElementById('planId').value;
    const data={
        name:document.getElementById('planName').value.trim(),
        amount:Number(document.getElementById('planAmount').value),
        due_day:Number(document.getElementById('planDueDay').value),
        is_default:document.getElementById('planDefault').checked,
        is_active:document.getElementById('planActive').checked,
        description:document.getElementById('planDescription').value.trim()||null
    };

    try{
        await api(
            id?`/api/finance/subscription-plans/${id}`:'/api/finance/subscription-plans',
            {
                method:id?'PUT':'POST',
                body:JSON.stringify(data)
            }
        );

        resetPlanForm();
        await loadPlans();
        showAlert('success',id?'Subscription plan updated.':'Subscription plan created.');
    }catch(error){
        showError('planError',extractError(error));
    }
});

function confirmDeletePlan(id,name){
    confirmAction({
        title:'Delete Subscription Plan?',
        message:`Delete "${name}"? This action cannot be undone.`,
        confirmText:'Delete',
        danger:true,
        onConfirm:async()=>{
            try{
                await api(`/api/finance/subscription-plans/${id}`,{
                    method:'DELETE'
                });
                await loadPlans();
                showAlert('success','Subscription plan deleted successfully.');
            }catch(error){
                showError('planError',extractError(error));
            }
        }
    });
}

function confirmBulkGenerate(){
    const month=Number(document.getElementById('monthFilter').value);
    const year=Number(document.getElementById('yearFilter').value);

    confirmAction({
        title:'Generate Monthly Dues?',
        message:`Generate subscription dues for all active members for ${monthName(month)} ${year}?`,
        confirmText:'Generate',
        onConfirm:()=>generateBulk(month,year)
    });
}

async function generateBulk(month,year){
    try{
        const response=await api('/api/finance/subscriptions/generate-bulk',{
            method:'POST',
            body:JSON.stringify({month,year})
        });

        const result=response.data??{};

        showAlert(
            result.failed>0?'error':'success',
            `Created: ${result.created??0}, Existing: ${result.existing??0}, Failed: ${result.failed??0}.`
        );

        loadPage(currentPage);
    }catch(error){
        showAlert('error',extractError(error));
    }
}

async function generateSingleDue(id){
    try{
        await api(`/api/finance/subscriptions/${id}/generate-due`,{
            method:'POST',
            body:JSON.stringify({
                year:Number(document.getElementById('yearFilter').value),
                month:Number(document.getElementById('monthFilter').value)
            })
        });

        showAlert('success','Monthly due generated successfully.');
        loadPage(currentPage);
    }catch(error){
        showAlert('error',extractError(error));
    }
}

function confirmDeactivate(id,name){
    confirmAction({
        title:'Deactivate Subscription?',
        message:`Deactivate the subscription for "${name}"? Existing dues and payments will remain unchanged.`,
        confirmText:'Deactivate',
        danger:true,
        onConfirm:async()=>{
            try{
                await api(`/api/finance/subscriptions/${id}/deactivate`,{
                    method:'POST'
                });
                showAlert('success','Subscription deactivated successfully.');
                loadPage(currentPage);
            }catch(error){
                showAlert('error',extractError(error));
            }
        }
    });
}

async function loadPayments(){
    openModal('paymentListModal');

    const body=document.getElementById('paymentBody');
    body.innerHTML=`<tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">Loading payments...</td></tr>`;

    try{
        const response=await api('/api/finance/subscription-payments?status=pending');
        payments=response.data?.data??[];
        renderPayments();
    }catch(error){
        body.innerHTML=`<tr><td colspan="7" class="px-4 py-10 text-center text-red-500">${escapeHtml(extractError(error))}</td></tr>`;
    }
}

function renderPayments(){
    const body=document.getElementById('paymentBody');

    if(!payments.length){
        body.innerHTML=`<tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">No pending payments.</td></tr>`;
        return;
    }

    body.innerHTML=payments.map(item=>`
        <tr class="border-t border-slate-100">
            <td class="px-4 py-3 font-medium text-slate-700">${escapeHtml(item.payment_no)}</td>
            <td class="px-4 py-3">
                <div>${escapeHtml(item.member?.user?.name??'—')}</div>
                <div class="text-xs text-slate-400">${escapeHtml(item.member?.member_code??'')}</div>
            </td>
            <td class="px-4 py-3">${monthName(item.due?.month)} ${item.due?.year??''}</td>
            <td class="px-4 py-3 text-right font-semibold">${money(item.amount)}</td>
            <td class="px-4 py-3">${titleCase(item.payment_method)}</td>
            <td class="px-4 py-3 text-slate-500">${escapeHtml(item.transaction_reference??'—')}</td>
            <td class="px-4 py-3">
                <div class="flex justify-end gap-1">
                    <button type="button" onclick="confirmVerifyPayment(${item.id},'${escapeJs(item.payment_no)}')" class="rounded-md bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">Verify</button>
                    <button type="button" onclick="openRejectPayment(${item.id})" class="rounded-md bg-red-50 px-2.5 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-100">Reject</button>
                </div>
            </td>
        </tr>
    `).join('');
}

function confirmVerifyPayment(id,number){
    confirmAction({
        title:'Verify Payment?',
        message:`Verify payment "${number}" and post it to the accounting ledger?`,
        confirmText:'Verify',
        onConfirm:async()=>{
            try{
                await api(`/api/finance/subscription-payments/${id}/verify`,{
                    method:'POST',
                    body:JSON.stringify({})
                });
                showAlert('success','Payment verified and posted successfully.');
                await loadPayments();
                loadPage(currentPage);
            }catch(error){
                showAlert('error',extractError(error));
            }
        }
    });
}

function openRejectPayment(id){
    document.getElementById('rejectPaymentId').value=id;
    document.getElementById('rejectReason').value='';
    clearError('rejectError');
    openModal('rejectModal');
}

document.getElementById('rejectForm').addEventListener('submit',async event=>{
    event.preventDefault();

    const id=document.getElementById('rejectPaymentId').value;
    const reason=document.getElementById('rejectReason').value.trim();

    try{
        await api(`/api/finance/subscription-payments/${id}/reject`,{
            method:'POST',
            body:JSON.stringify({reason})
        });

        closeModal('rejectModal');
        showAlert('success','Payment rejected successfully.');
        await loadPayments();
        loadPage(currentPage);
    }catch(error){
        showError('rejectError',extractError(error));
    }
});

function openModal(id){
    const modal=document.getElementById(id);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeModal(id){
    const modal=document.getElementById(id);
    modal.classList.add('hidden');
    modal.classList.remove('flex');

    if(!document.querySelector('.app-modal-overlay.flex')){
        document.body.classList.remove('overflow-hidden');
    }
}

function confirmAction(options){
    if(window.AdminUI?.confirm){
        AdminUI.confirm(options);
        return;
    }

    if(confirm(options.message)){
        options.onConfirm?.();
    }
}

function showAlert(type,message){
    const box=document.getElementById('pageAlert');
    box.className=type==='success'
        ?'rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700'
        :'rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600';
    box.textContent=message;
    box.classList.remove('hidden');
}

function showError(id,message){
    const box=document.getElementById(id);
    box.textContent=message;
    box.classList.remove('hidden');
}

function clearError(id){
    const box=document.getElementById(id);
    box.textContent='';
    box.classList.add('hidden');
}

function extractError(error){
    if(error.data?.errors){
        const errors=Object.values(error.data.errors).flat();
        if(errors.length)return errors.join(' ');
    }
    return error.data?.message||error.message||'Something went wrong.';
}

function escapeHtml(value){
    const div=document.createElement('div');
    div.textContent=value??'';
    return div.innerHTML;
}

function escapeJs(value){
    return String(value??'')
        .replaceAll('\\','\\\\')
        .replaceAll("'","\\'")
        .replaceAll('\n',' ');
}

function titleCase(value){
    return String(value??'')
        .replaceAll('_',' ')
        .replace(/\b\w/g,char=>char.toUpperCase());
}

function monthName(month){
    if(!month)return '—';

    return new Date(2000,Number(month)-1,1)
        .toLocaleString('en',{month:'short'});
}

function money(value){
    return '৳'+Number(value??0).toLocaleString('en-US',{
        minimumFractionDigits:2,
        maximumFractionDigits:2
    });
}

function dueBadge(status){
    const styles={
        paid:'bg-emerald-50 text-emerald-700',
        partial:'bg-amber-50 text-amber-700',
        unpaid:'bg-red-50 text-red-700',
        waived:'bg-slate-100 text-slate-600'
    };

    return`
        <span class="inline-flex rounded-md px-2 py-1 text-[10px] font-semibold ${styles[status]??styles.unpaid}">
            ${titleCase(status)}
        </span>
    `;
}
</script>
@endpush