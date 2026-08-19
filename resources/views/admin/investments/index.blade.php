@extends('layouts.admin')

@section('title','Investments')
@section('page_title','Investments')

@section('content')

<div class="space-y-5">

    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white p-5 shadow-sm md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-graph-up-arrow"></i>
            </div>

            <div>
                <h1 class="text-xl font-bold text-slate-800">Investment Management</h1>
                <p class="mt-1 text-sm text-slate-500">Manage member investments, returns and investment status.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Investment.create'))
            <button type="button" onclick="openInvestmentModal()" class="inline-flex w-fit items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                <i class="bi bi-plus-lg"></i>
                Add Investment
            </button>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="rounded-md border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Total Investment</p>
            <p id="totalAmount" class="mt-2 text-xl font-bold text-slate-800">৳0</p>
        </div>

        <div class="rounded-md border border-indigo-200 bg-indigo-50/40 p-4">
            <p class="text-xs text-indigo-600">Active</p>
            <p id="activeCount" class="mt-2 text-xl font-bold text-indigo-700">0</p>
        </div>

        <div class="rounded-md border border-emerald-200 bg-emerald-50/40 p-4">
            <p class="text-xs text-emerald-600">Paid Return</p>
            <p id="paidReturn" class="mt-2 text-xl font-bold text-emerald-700">৳0</p>
        </div>

        <div class="rounded-md border border-amber-200 bg-amber-50/40 p-4">
            <p class="text-xs text-amber-600">Pending</p>
            <p id="pendingCount" class="mt-2 text-xl font-bold text-amber-700">0</p>
        </div>
    </div>

   <div class="rounded-md border border-slate-200 bg-white p-3">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-search text-sm"></i>
            </div>

            <div>
                <p class="text-sm font-semibold text-slate-700">Search Investments</p>
                <p class="hidden text-[11px] text-slate-400 sm:block">
                    Search by investment number, title or member
                </p>
            </div>
        </div>

        <div class="flex w-full items-center lg:w-auto">
            <div class="relative w-full lg:w-80">
                <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>

                <input
                    id="searchInput"
                    type="text"
                    placeholder="Search investments..."
                    class="h-9 w-full rounded-l-md border border-r-0 border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                >
            </div>

            <select
                id="statusFilter"
                class="h-9 cursor-pointer border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400"
            >
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>

            <button
                type="button"
                onclick="clearFilters()"
                class="inline-flex h-9 shrink-0 cursor-pointer items-center gap-1.5 rounded-r-md border border-l-0 border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-100"
            >
                <i class="bi bi-x-lg text-[10px]"></i>
                Clear
            </button>
        </div>
    </div>
</div>

    <div class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Investment</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Member</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Amount</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Expected Return</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Paid Return</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>

                <tbody id="investmentTable">
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-400">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- Investment Modal --}}
<div id="investmentModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">

        <div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-5">
            <div>
                <h2 id="investmentModalTitle" class="text-lg font-bold text-slate-800">Add Investment</h2>
                <p class="mt-1 text-xs text-slate-500">Create or update a member investment.</p>
            </div>

            <button type="button" onclick="closeInvestmentModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="investmentForm" class="flex min-h-0 flex-1 flex-col">
            <div class="space-y-4 overflow-y-auto p-5">

                <div>
                    <label class="form-label">Member *</label>
                    <select id="memberId" class="app-input">
                        <option value="">Select Member</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Title *</label>
                    <input id="title" maxlength="255" class="app-input" placeholder="Investment title">
                </div>

                <div>
                    <label class="form-label">Description</label>
                    <textarea id="description" rows="3" class="app-input resize-none"></textarea>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Investment Amount *</label>
                        <input id="amount" type="number" min="0.01" step="0.01" class="app-input">
                    </div>

                    <div>
                        <label class="form-label">Expected Return</label>
                        <input id="expectedReturn" type="number" min="0" step="0.01" class="app-input">
                    </div>

                    <div>
                        <label class="form-label">Investment Date *</label>
                        <input id="investmentDate" type="date" class="app-input">
                    </div>

                    <div>
                        <label class="form-label">Maturity Date</label>
                        <input id="maturityDate" type="date" class="app-input">
                    </div>

                    <div>
                        <label class="form-label">Status</label>

                        <select id="status" class="app-input">
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <div id="investmentError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-700"></div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="closeInvestmentModal()" class="rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600">
                    Cancel
                </button>

                <button id="saveInvestmentButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                    Save Investment
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Return Modal --}}
<div id="returnModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel w-full max-w-lg overflow-hidden rounded-md bg-white">

        <div class="flex items-start justify-between border-b border-slate-200 px-5 py-5">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Add Return</h2>
                <p id="returnInvestmentInfo" class="mt-1 text-xs text-slate-500"></p>
            </div>

            <button type="button" onclick="AdminUI.closeModal('returnModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="returnForm">
            <div class="space-y-4 p-5">
                <input id="returnInvestmentId" type="hidden">

                <div>
                    <label class="form-label">Amount *</label>
                    <input id="returnAmount" type="number" min="0.01" step="0.01" class="app-input">
                </div>

                <div>
                    <label class="form-label">Return Date *</label>
                    <input id="returnDate" type="date" class="app-input">
                </div>

                <div>
                    <label class="form-label">Status *</label>

                    <select id="returnStatus" class="app-input">
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Description</label>
                    <textarea id="returnDescription" rows="3" class="app-input resize-none"></textarea>
                </div>

                <div id="returnError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-700"></div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="AdminUI.closeModal('returnModal')" class="rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600">
                    Cancel
                </button>

                <button id="saveReturnButton" type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                    Save Return
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.form-label{
    display:block;
    margin-bottom:.4rem;
    font-size:.8rem;
    font-weight:600;
    color:rgb(51 65 85);
}
</style>

<script>
let investments=[];
let editingInvestment=null;
let currentPage=1;
let lastPage=1;
let total=0;

const canUpdate=@json(auth()->user()->hasPermission('Investment.update'));
const canDelete=@json(auth()->user()->hasPermission('Investment.delete'));

const currency=@json(setting('currency_symbol','৳'));

const el={
    table:document.getElementById('investmentTable'),
    search:document.getElementById('searchInput'),
    statusFilter:document.getElementById('statusFilter'),
    form:document.getElementById('investmentForm'),
    memberId:document.getElementById('memberId'),
    title:document.getElementById('title'),
    description:document.getElementById('description'),
    amount:document.getElementById('amount'),
    expectedReturn:document.getElementById('expectedReturn'),
    investmentDate:document.getElementById('investmentDate'),
    maturityDate:document.getElementById('maturityDate'),
    status:document.getElementById('status'),
    saveButton:document.getElementById('saveInvestmentButton')
};

function money(value){
    return currency+Number(value??0).toLocaleString('en-US',{
        minimumFractionDigits:2,
        maximumFractionDigits:2
    });
}

async function loadInvestments(page=1){
    currentPage=page;

    const query=AdminUI.query({
        search:el.search.value.trim(),
        status:el.statusFilter.value,
        page
    });

    try{
        const response=await api(`/api/investments?${query}`);
        const paginator=response.data??{};

        investments=paginator.data??[];
        currentPage=paginator.current_page??1;
        lastPage=paginator.last_page??1;
        total=paginator.total??0;

        renderInvestments();

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total,
            onPageChange:loadInvestments
        });
    }catch(error){
        el.table.innerHTML=AdminUI.emptyState(
            AdminUI.extractError(error),
            7
        );
    }
}

async function loadStatistics(){
    try{
        const response=await api('/api/investments/statistics');
        const stats=response.data??{};

        document.getElementById('totalAmount').innerText=money(stats.total_amount);
        document.getElementById('paidReturn').innerText=money(stats.paid_return);
        document.getElementById('activeCount').innerText=stats.active??0;
        document.getElementById('pendingCount').innerText=stats.pending??0;
    }catch(error){
        console.error(error);
    }
}

function renderInvestments(){
    if(!investments.length){
        el.table.innerHTML=AdminUI.emptyState(
            'No investments found.',
            7
        );

        return;
    }

    el.table.innerHTML=investments.map(item=>`
        <tr class="border-b border-slate-100 hover:bg-slate-50">
            <td class="px-4 py-4">
                <p class="text-xs font-bold text-indigo-600">
                    ${AdminUI.escapeHtml(item.investment_no)}
                </p>

                <p class="mt-1 max-w-[220px] truncate font-semibold text-slate-800">
                    ${AdminUI.escapeHtml(item.title)}
                </p>

                <p class="mt-1 text-[10px] text-slate-400">
                    ${AdminUI.formatDate(item.investment_date)}
                </p>
            </td>

            <td class="px-4 py-4">
                <p class="text-xs font-semibold text-slate-700">
                    ${AdminUI.escapeHtml(item.member?.user?.name??'N/A')}
                </p>

                <p class="mt-1 text-[10px] text-slate-400">
                    ${AdminUI.escapeHtml(item.member?.member_code??'')}
                </p>
            </td>

            <td class="px-4 py-4 text-right text-xs font-bold text-slate-700">
                ${money(item.amount)}
            </td>

            <td class="px-4 py-4 text-right text-xs font-semibold text-slate-600">
                ${money(item.expected_return)}
            </td>

            <td class="px-4 py-4 text-right text-xs font-semibold text-emerald-600">
                ${money(item.paid_return_total)}
            </td>

            <td class="px-4 py-4">
                ${AdminUI.statusBadge(item.status)}
            </td>

            <td class="px-4 py-4">
                <div class="flex justify-end gap-1">
                    ${canUpdate&&item.status!=='cancelled'?`
                        <button type="button" onclick="openReturnModal(${item.id})" title="Add Return" class="flex h-8 w-8 items-center justify-center rounded-md bg-emerald-50 text-emerald-700 hover:bg-emerald-100">
                            <i class="bi bi-cash-coin text-xs"></i>
                        </button>
                    `:''}

                    ${canUpdate?`
                        <button type="button" onclick="editInvestment(${item.id})" title="Edit" class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-100">
                            <i class="bi bi-pencil-square text-xs"></i>
                        </button>
                    `:''}

                    ${canDelete?`
                        <button type="button" onclick="deleteInvestment(${item.id})" title="Delete" class="flex h-8 w-8 items-center justify-center rounded-md bg-red-50 text-red-600 hover:bg-red-100">
                            <i class="bi bi-trash text-xs"></i>
                        </button>
                    `:''}
                </div>
            </td>
        </tr>
    `).join('');
}

async function loadMembers(selectedId=null){
    try{
        const response=await api('/api/investments/members');
        const members=response.data??[];

        el.memberId.innerHTML='<option value="">Select Member</option>';

        members.forEach(member=>{
            const option=document.createElement('option');

            option.value=member.id;
            option.textContent=`${member.member_code} - ${member.user?.name??'Member'}`;

            el.memberId.appendChild(option);
        });

        if(selectedId){
            el.memberId.value=String(selectedId);
        }
    }catch(error){
        Toast.error('Unable to load members.');
    }
}

async function openInvestmentModal(item=null){
    editingInvestment=item;

    AdminUI.resetForm(el.form);
    AdminUI.clearError('investmentError');

    document.getElementById('investmentModalTitle').innerText=
        item?'Edit Investment':'Add Investment';

    el.saveButton.innerText=
        item?'Update Investment':'Save Investment';

    await loadMembers(item?.member_id??null);

    if(item){
        el.title.value=item.title??'';
        el.description.value=item.description??'';
        el.amount.value=item.amount??'';
        el.expectedReturn.value=item.expected_return??0;
        el.investmentDate.value=(item.investment_date??'').substring(0,10);
        el.maturityDate.value=(item.maturity_date??'').substring(0,10);
        el.status.value=item.status??'pending';
    }else{
        el.status.value='pending';
        el.investmentDate.value=new Date().toISOString().slice(0,10);
    }

    AdminUI.openModal('investmentModal');
}

function closeInvestmentModal(){
    AdminUI.closeModal('investmentModal');
    editingInvestment=null;
}

function editInvestment(id){
    const item=investments.find(
        item=>Number(item.id)===Number(id)
    );

    if(item){
        openInvestmentModal(item);
    }
}

el.form.addEventListener('submit',async event=>{
    event.preventDefault();

    AdminUI.clearError('investmentError');

    const data={
        member_id:Number(el.memberId.value),
        title:el.title.value.trim(),
        description:el.description.value.trim()||null,
        amount:Number(el.amount.value),
        expected_return:Number(el.expectedReturn.value||0),
        investment_date:el.investmentDate.value,
        maturity_date:el.maturityDate.value||null,
        status:el.status.value
    };

    AdminUI.setLoading(
        el.saveButton,
        editingInvestment?'Updating...':'Saving...'
    );

    try{
        const wasEditing=Boolean(editingInvestment);

        await api(
            wasEditing
                ?`/api/investments/${editingInvestment.id}`
                :'/api/investments',
            {
                method:wasEditing?'PUT':'POST',
                body:JSON.stringify(data)
            }
        );

        closeInvestmentModal();

        Toast.success(
            wasEditing
                ?'Investment updated successfully.'
                :'Investment created successfully.'
        );

        await Promise.all([
            loadInvestments(wasEditing?currentPage:1),
            loadStatistics()
        ]);
    }catch(error){
        AdminUI.showError(
            'investmentError',
            AdminUI.extractError(error)
        );
    }finally{
        AdminUI.resetLoading(el.saveButton);
    }
});

function deleteInvestment(id){
    AdminUI.deleteRequest(
        `/api/investments/${id}`,
        {
            message:'Delete this investment permanently?',
            successMessage:'Investment deleted successfully.',
            onSuccess:async()=>{
                await Promise.all([
                    loadInvestments(currentPage),
                    loadStatistics()
                ]);
            }
        }
    );
}

function openReturnModal(id){
    const item=investments.find(
        item=>Number(item.id)===Number(id)
    );

    if(!item)return;

    document.getElementById('returnForm').reset();
    AdminUI.clearError('returnError');

    document.getElementById('returnInvestmentId').value=item.id;
    document.getElementById('returnInvestmentInfo').innerText=
        `${item.investment_no} • ${item.title}`;

    document.getElementById('returnDate').value=
        new Date().toISOString().slice(0,10);

    document.getElementById('returnStatus').value='paid';

    AdminUI.openModal('returnModal');
}

document.getElementById('returnForm').addEventListener('submit',async event=>{
    event.preventDefault();

    AdminUI.clearError('returnError');

    const id=document.getElementById('returnInvestmentId').value;
    const button=document.getElementById('saveReturnButton');

    const data={
        amount:Number(document.getElementById('returnAmount').value),
        return_date:document.getElementById('returnDate').value,
        status:document.getElementById('returnStatus').value,
        description:document.getElementById('returnDescription').value.trim()||null
    };

    AdminUI.setLoading(button,'Saving...');

    try{
        await api(
            `/api/investments/${id}/returns`,
            {
                method:'POST',
                body:JSON.stringify(data)
            }
        );

        AdminUI.closeModal('returnModal');
        Toast.success('Investment return added successfully.');

        await Promise.all([
            loadInvestments(currentPage),
            loadStatistics()
        ]);
    }catch(error){
        AdminUI.showError(
            'returnError',
            AdminUI.extractError(error)
        );
    }finally{
        AdminUI.resetLoading(button);
    }
});

function clearFilters(){
    el.search.value='';
    el.statusFilter.value='';
    loadInvestments(1);
}

async function initInvestmentPage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(initInvestmentPage,50);
        return;
    }

    el.search.addEventListener(
        'input',
        AdminUI.debounce(()=>loadInvestments(1))
    );

    el.statusFilter.addEventListener(
        'change',
        ()=>loadInvestments(1)
    );

    await Promise.all([
        loadInvestments(),
        loadStatistics()
    ]);
}

if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initInvestmentPage
    );
}else{
    initInvestmentPage();
}
</script>

@endsection