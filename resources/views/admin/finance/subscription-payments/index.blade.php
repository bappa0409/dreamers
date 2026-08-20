@extends('layouts.admin')

@section('title','Subscription Payments')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Subscription Payments</h4>
            <p class="text-muted mb-0">
                Review and verify member subscription payments.
            </p>
        </div>

        <button
            type="button"
            class="btn btn-outline-primary"
            onclick="loadPayments()"
        >
            <i class="bi bi-arrow-clockwise me-1"></i>
            Refresh
        </button>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">Total</small>
                    <h4 class="mb-0 mt-1" id="totalCount">0</h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">Pending</small>
                    <h4 class="mb-0 mt-1 text-warning" id="pendingCount">0</h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">Verified</small>
                    <h4 class="mb-0 mt-1 text-success" id="verifiedCount">0</h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">Rejected</small>
                    <h4 class="mb-0 mt-1 text-danger" id="rejectedCount">0</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body border-bottom">
            <div class="row g-2">
                <div class="col-12 col-md-6">
                    <input
                        type="text"
                        class="form-control"
                        id="searchInput"
                        placeholder="Search member, ID, payment no, reference..."
                    >
                </div>

                <div class="col-8 col-md-4">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Payments</option>
                        <option value="pending">Pending</option>
                        <option value="verified">Verified</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div class="col-4 col-md-2 d-grid">
                    <button
                        type="button"
                        class="btn btn-primary"
                        onclick="applyFilter()"
                    >
                        Filter
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Payment</th>
                        <th>Member</th>
                        <th>Period</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>

                <tbody id="paymentTable">
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white">
            <div
                class="d-flex justify-content-between align-items-center gap-2"
                id="pagination"
            ></div>
        </div>
    </div>
</div>

<div
    class="modal fade"
    id="paymentModal"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header">
                <h5 class="modal-title">
                    Payment Details
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>
            </div>

            <div class="modal-body" id="paymentDetails"></div>

            <div
                class="modal-footer"
                id="paymentActions"
            ></div>
        </div>
    </div>
</div>

<div
    class="modal fade"
    id="rejectModal"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header">
                <h5 class="modal-title">
                    Reject Payment
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>
            </div>

            <div class="modal-body">
                <label class="form-label">
                    Rejection Reason
                </label>

                <textarea
                    class="form-control"
                    id="rejectReason"
                    rows="4"
                    maxlength="1000"
                    placeholder="Enter rejection reason..."
                ></textarea>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-light"
                    data-bs-dismiss="modal"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="btn btn-danger"
                    id="confirmRejectButton"
                    onclick="confirmReject()"
                >
                    Reject Payment
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPage=1;
let selectedPayment=null;
let paymentModal=null;
let rejectModal=null;

document.addEventListener('DOMContentLoaded',()=>{
    paymentModal=new bootstrap.Modal(
        document.getElementById('paymentModal')
    );

    rejectModal=new bootstrap.Modal(
        document.getElementById('rejectModal')
    );

    document
        .getElementById('searchInput')
        .addEventListener('keydown',event=>{
            if(event.key==='Enter'){
                applyFilter();
            }
        });

    loadPayments();
});

async function apiRequest(url,options={}){
    const response=await fetch(url,{
        ...options,
        headers:{
            'Accept':'application/json',
            'Content-Type':'application/json',
            'X-Requested-With':'XMLHttpRequest',
            ...(options.headers||{})
        }
    });

    const data=await response.json().catch(()=>({}));

    if(!response.ok){
        let message=data.message||'Something went wrong.';

        if(data.errors){
            const errors=Object.values(data.errors).flat();

            if(errors.length){
                message=errors[0];
            }
        }

        throw new Error(message);
    }

    return data;
}

function applyFilter(){
    currentPage=1;
    loadPayments();
}

async function loadPayments(page=currentPage){
    currentPage=page;

    const search=document
        .getElementById('searchInput')
        .value
        .trim();

    const status=document
        .getElementById('statusFilter')
        .value;

    const params=new URLSearchParams({
        page:currentPage,
        per_page:20
    });

    if(search){
        params.set('search',search);
    }

    if(status){
        params.set('status',status);
    }

    const table=document.getElementById('paymentTable');

    table.innerHTML=`
        <tr>
            <td colspan="7" class="text-center py-5 text-muted">
                Loading...
            </td>
        </tr>
    `;

    try{
        const response=await apiRequest(
            `/api/subscription-payments?${params.toString()}`
        );

        const paginator=response.data;
        const payments=paginator.data||[];

        renderPayments(payments);
        renderPagination(paginator);

        document.getElementById('totalCount').textContent=
            paginator.total??payments.length;

        await loadStatusCounts();
    }catch(error){
        table.innerHTML=`
            <tr>
                <td colspan="7" class="text-center py-5 text-danger">
                    ${escapeHtml(error.message)}
                </td>
            </tr>
        `;
    }
}

async function loadStatusCounts(){
    try{
        const [pending,verified,rejected]=await Promise.all([
            apiRequest(
                '/api/subscription-payments?status=pending&per_page=5'
            ),
            apiRequest(
                '/api/subscription-payments?status=verified&per_page=5'
            ),
            apiRequest(
                '/api/subscription-payments?status=rejected&per_page=5'
            )
        ]);

        document.getElementById('pendingCount').textContent=
            pending.data.total??0;

        document.getElementById('verifiedCount').textContent=
            verified.data.total??0;

        document.getElementById('rejectedCount').textContent=
            rejected.data.total??0;
    }catch(error){
        console.error(error);
    }
}

function renderPayments(payments){
    const table=document.getElementById('paymentTable');

    if(!payments.length){
        table.innerHTML=`
            <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                    No subscription payments found.
                </td>
            </tr>
        `;

        return;
    }

    table.innerHTML=payments.map(payment=>{
        const member=payment.member||{};
        const user=member.user||{};
        const due=payment.due||{};

        return`
            <tr>
                <td>
                    <div class="fw-semibold">
                        ${escapeHtml(payment.payment_no||'-')}
                    </div>

                    <small class="text-muted">
                        ${formatDate(payment.paid_at)}
                    </small>
                </td>

                <td>
                    <div class="fw-semibold">
                        ${escapeHtml(user.name||'-')}
                    </div>

                    <small class="text-muted">
                        ${escapeHtml(member.member_code||'')}
                    </small>
                </td>

                <td>
                    ${monthName(due.month)} ${due.year||''}
                </td>

                <td class="fw-semibold">
                    ৳${money(payment.amount)}
                </td>

                <td>
                    ${paymentMethod(payment.payment_method)}
                </td>

                <td>
                    ${statusBadge(payment.status)}
                </td>

                <td class="text-end">
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary"
                        onclick="showPayment(${payment.id})"
                    >
                        View
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

async function showPayment(id){
    try{
        const response=await apiRequest(
            `/api/subscription-payments/${id}`
        );

        selectedPayment=response.data;

        renderPaymentDetails(selectedPayment);

        paymentModal.show();
    }catch(error){
        showMessage(error.message,'danger');
    }
}

function renderPaymentDetails(payment){
    const member=payment.member||{};
    const user=member.user||{};
    const due=payment.due||{};

    document.getElementById('paymentDetails').innerHTML=`
        <div class="row g-3">
            <div class="col-md-6">
                ${detail(
                    'Member',
                    `${user.name||'-'} (${member.member_code||'-'})`
                )}
            </div>

            <div class="col-md-6">
                ${detail(
                    'Payment No',
                    payment.payment_no||'-'
                )}
            </div>

            <div class="col-md-6">
                ${detail(
                    'Subscription Period',
                    `${monthName(due.month)} ${due.year||''}`
                )}
            </div>

            <div class="col-md-6">
                ${detail(
                    'Payment Amount',
                    `৳${money(payment.amount)}`
                )}
            </div>

            <div class="col-md-6">
                ${detail(
                    'Payment Method',
                    paymentMethod(payment.payment_method)
                )}
            </div>

            <div class="col-md-6">
                ${detail(
                    'Status',
                    statusBadge(payment.status),
                    false
                )}
            </div>

            <div class="col-md-6">
                ${detail(
                    'Transaction Reference',
                    payment.transaction_reference||'-'
                )}
            </div>

            <div class="col-md-6">
                ${detail(
                    'Submitted',
                    formatDateTime(payment.paid_at)
                )}
            </div>

            <div class="col-12">
                <hr>
            </div>

            <div class="col-md-3">
                ${detail(
                    'Base Amount',
                    `৳${money(due.base_amount)}`
                )}
            </div>

            <div class="col-md-3">
                ${detail(
                    'Shares',
                    due.share_count??1
                )}
            </div>

            <div class="col-md-3">
                ${detail(
                    'Fine',
                    `৳${money(due.fine_amount)}`
                )}
            </div>

            <div class="col-md-3">
                ${detail(
                    'Total Due',
                    `৳${money(due.amount)}`
                )}
            </div>

            ${
                payment.verifier
                    ?`
                        <div class="col-md-6">
                            ${detail(
                                'Processed By',
                                payment.verifier.name||'-'
                            )}
                        </div>

                        <div class="col-md-6">
                            ${detail(
                                'Processed At',
                                formatDateTime(
                                    payment.verified_at
                                )
                            )}
                        </div>
                    `
                    :''
            }

            ${
                payment.verification_note
                    ?`
                        <div class="col-12">
                            ${detail(
                                payment.status==='rejected'
                                    ?'Rejection Reason'
                                    :'Verification Note',
                                payment.verification_note
                            )}
                        </div>
                    `
                    :''
            }
        </div>
    `;

    const actions=document.getElementById(
        'paymentActions'
    );

    if(payment.status==='pending'){
        actions.innerHTML=`
            <button
                type="button"
                class="btn btn-outline-danger"
                onclick="openReject()"
            >
                Reject
            </button>

            <button
                type="button"
                class="btn btn-success"
                id="verifyButton"
                onclick="verifyPayment()"
            >
                Verify Payment
            </button>
        `;
    }else{
        actions.innerHTML=`
            <button
                type="button"
                class="btn btn-light"
                data-bs-dismiss="modal"
            >
                Close
            </button>
        `;
    }
}

async function verifyPayment(){
    if(!selectedPayment){
        return;
    }

    if(!confirm(
        `Verify payment ${selectedPayment.payment_no}?`
    )){
        return;
    }

    const button=document.getElementById('verifyButton');

    button.disabled=true;
    button.innerHTML=`
        <span
            class="spinner-border spinner-border-sm me-1"
        ></span>
        Verifying...
    `;

    try{
        await apiRequest(
            `/api/subscription-payments/${selectedPayment.id}/verify`,
            {
                method:'POST',
                body:JSON.stringify({
                    note:null
                })
            }
        );

        paymentModal.hide();

        showMessage(
            'Payment verified successfully.',
            'success'
        );

        await loadPayments();
    }catch(error){
        showMessage(error.message,'danger');

        button.disabled=false;
        button.textContent='Verify Payment';
    }
}

function openReject(){
    if(!selectedPayment){
        return;
    }

    document.getElementById('rejectReason').value='';

    paymentModal.hide();

    setTimeout(()=>{
        rejectModal.show();
    },250);
}

async function confirmReject(){
    if(!selectedPayment){
        return;
    }

    const reason=document
        .getElementById('rejectReason')
        .value
        .trim();

    if(!reason){
        showMessage(
            'Rejection reason is required.',
            'danger'
        );

        return;
    }

    const button=document.getElementById(
        'confirmRejectButton'
    );

    button.disabled=true;
    button.innerHTML=`
        <span
            class="spinner-border spinner-border-sm me-1"
        ></span>
        Rejecting...
    `;

    try{
        await apiRequest(
            `/api/subscription-payments/${selectedPayment.id}/reject`,
            {
                method:'POST',
                body:JSON.stringify({
                    reason
                })
            }
        );

        rejectModal.hide();

        showMessage(
            'Payment rejected successfully.',
            'success'
        );

        await loadPayments();
    }catch(error){
        showMessage(error.message,'danger');
    }finally{
        button.disabled=false;
        button.textContent='Reject Payment';
    }
}

function renderPagination(data){
    const container=document.getElementById(
        'pagination'
    );

    const current=data.current_page??1;
    const last=data.last_page??1;

    container.innerHTML=`
        <small class="text-muted">
            Showing ${data.from??0}-${data.to??0}
            of ${data.total??0}
        </small>

        <div class="btn-group">
            <button
                type="button"
                class="btn btn-sm btn-outline-secondary"
                ${current<=1?'disabled':''}
                onclick="loadPayments(${current-1})"
            >
                Previous
            </button>

            <button
                type="button"
                class="btn btn-sm btn-light"
                disabled
            >
                ${current} / ${last}
            </button>

            <button
                type="button"
                class="btn btn-sm btn-outline-secondary"
                ${current>=last?'disabled':''}
                onclick="loadPayments(${current+1})"
            >
                Next
            </button>
        </div>
    `;
}

function detail(label,value,escape=true){
    return`
        <div class="text-muted small mb-1">
            ${label}
        </div>

        <div class="fw-semibold">
            ${escape
                ?escapeHtml(String(value??'-'))
                :value
            }
        </div>
    `;
}

function statusBadge(status){
    const classes={
        pending:'warning',
        verified:'success',
        rejected:'danger'
    };

    return`
        <span class="badge bg-${classes[status]||'secondary'}">
            ${escapeHtml(
                String(status||'unknown')
                    .replaceAll('_',' ')
                    .replace(/\b\w/g,char=>char.toUpperCase())
            )}
        </span>
    `;
}

function paymentMethod(method){
    return String(method||'-')
        .replaceAll('_',' ')
        .replace(/\b\w/g,char=>char.toUpperCase());
}

function monthName(month){
    const months=[
        '',
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December'
    ];

    return months[Number(month)]||'-';
}

function money(value){
    return Number(value||0).toLocaleString(
        'en-BD',
        {
            minimumFractionDigits:2,
            maximumFractionDigits:2
        }
    );
}

function formatDate(value){
    if(!value){
        return '-';
    }

    return new Date(value).toLocaleDateString(
        'en-BD'
    );
}

function formatDateTime(value){
    if(!value){
        return '-';
    }

    return new Date(value).toLocaleString(
        'en-BD'
    );
}

function escapeHtml(value){
    const div=document.createElement('div');
    div.textContent=value??'';
    return div.innerHTML;
}

function showMessage(message,type='success'){
    const alert=document.createElement('div');

    alert.className=
        `alert alert-${type} alert-dismissible fade show position-fixed`;

    alert.style.cssText=
        'top:20px;right:20px;z-index:9999;max-width:420px;';

    alert.innerHTML=`
        ${escapeHtml(message)}
        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
        ></button>
    `;

    document.body.appendChild(alert);

    setTimeout(()=>{
        alert.remove();
    },4000);
}
</script>
@endpush