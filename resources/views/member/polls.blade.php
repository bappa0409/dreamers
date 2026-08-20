@extends('layouts.member')

@section('title','Polls')
@section('page_title','Polls')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-ui-checks-grid"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Member Polls</h1>
                <p class="text-sm text-slate-500">Participate in active polls and view your voting history.</p>
            </div>
        </div>

        <button
            type="button"
            onclick="loadPolls()"
            class="flex h-9 items-center justify-center gap-1.5 rounded-md border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
            <i class="bi bi-arrow-clockwise"></i>
            Refresh
        </button>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Active Polls</p>
            <p id="summaryActive" class="mt-1 text-xl font-bold text-slate-800">0</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Available to Vote</p>
            <p id="summaryAvailable" class="mt-1 text-xl font-bold text-slate-800">0</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Voted</p>
            <p id="summaryVoted" class="mt-1 text-xl font-bold text-slate-800">0</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Voting History</p>
            <p id="summaryHistory" class="mt-1 text-xl font-bold text-slate-800">0</p>
        </div>
    </div>

    <div id="pollContainer" class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <div class="col-span-full rounded-md border border-slate-200 bg-white py-12 text-center text-sm text-slate-400">
            Loading polls...
        </div>
    </div>
</div>

{{-- Vote Modal --}}
<div id="voteModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[90vh] w-full max-w-xl flex-col overflow-hidden rounded-md bg-white">
        <div class="flex shrink-0 items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Submit Vote</h2>
                <p class="text-xs text-slate-500">Select one option and submit your vote.</p>
            </div>

            <button
                type="button"
                onclick="closeVoteModal()"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto p-5">
            <div class="mb-5">
                <h3 id="votePollTitle" class="text-sm font-bold text-slate-800"></h3>
                <p id="votePollDescription" class="mt-1 whitespace-pre-line text-xs leading-5 text-slate-500"></p>
            </div>

            <div id="voteOptions" class="space-y-2"></div>

            <div id="voteError" class="mt-4 hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-600"></div>
        </div>

        <div class="flex shrink-0 justify-end gap-2 border-t border-slate-200 bg-white px-5 py-4">
            <button
                type="button"
                onclick="closeVoteModal()"
                class="h-9 rounded-md border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                Cancel
            </button>

            <button
                id="submitVoteButton"
                type="button"
                onclick="submitVote()"
                class="h-9 rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60">
                <i class="bi bi-check2-circle me-1"></i>
                Submit Vote
            </button>
        </div>
    </div>
</div>

{{-- Voting History Modal --}}
<div id="historyModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">
        <div class="flex shrink-0 items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Voting History</h2>
                <p class="text-xs text-slate-500">Your submitted poll votes.</p>
            </div>

            <button
                type="button"
                onclick="closeHistoryModal()"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="historyBody" class="min-h-0 flex-1 overflow-y-auto p-5"></div>

        <div class="flex shrink-0 justify-end border-t border-slate-200 bg-white px-5 py-4">
            <button
                type="button"
                onclick="closeHistoryModal()"
                class="h-9 rounded-md border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                Close
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let memberPolls=[];
let votingHistory=[];
let selectedPoll=null;
let selectedOptionId=null;

async function loadPolls(){
    const container=document.getElementById('pollContainer');

    container.innerHTML=`
        <div class="col-span-full rounded-md border border-slate-200 bg-white py-12 text-center text-sm text-slate-400">
            <i class="bi bi-arrow-repeat me-1 animate-spin"></i>
            Loading polls...
        </div>
    `;

    try{
        const response=await api('/api/member/polls');
        const data=response.data??{};

        memberPolls=data.active_polls??[];
        votingHistory=data.voting_history??[];

        updateSummary(data.summary??{});
        renderPolls();
    }catch(error){
        container.innerHTML=`
            <div class="col-span-full rounded-md border border-red-200 bg-red-50 py-12 text-center text-sm text-red-600">
                ${escapeValue(AdminUI.extractError(error))}
            </div>
        `;
    }
}

function updateSummary(summary){
    const votedCount=memberPolls.filter(
        poll=>Boolean(poll.has_voted)
    ).length;

    const availableCount=memberPolls.filter(
        poll=>!Boolean(poll.has_voted)
    ).length;

    document.getElementById('summaryActive').textContent=
        Number(summary.active_polls??memberPolls.length);

    document.getElementById('summaryAvailable').textContent=
        availableCount;

    document.getElementById('summaryVoted').textContent=
        votedCount;

    document.getElementById('summaryHistory').textContent=
        Number(summary.total_votes??votingHistory.length);
}

function renderPolls(){
    const container=document.getElementById('pollContainer');

    if(!memberPolls.length){
        container.innerHTML=`
            <div class="col-span-full rounded-md border border-slate-200 bg-white px-4 py-12 text-center">
                <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <i class="bi bi-ui-checks-grid"></i>
                </div>

                <p class="mt-3 text-sm font-semibold text-slate-600">
                    No active polls available
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    There are currently no active polls available for voting.
                </p>
            </div>
        `;
        return;
    }

    container.innerHTML=memberPolls.map(poll=>{
        const voted=Boolean(poll.has_voted);
        const options=poll.options??[];
        const selectedOptionId=Number(poll.my_vote_option_id??0);

        return`
            <div class="flex flex-col overflow-hidden rounded-md border border-slate-200 bg-white">
                <div class="flex-1 p-5">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold leading-5 text-slate-800">
                                ${escapeValue(poll.title??'Untitled Poll')}
                            </h3>

                            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-[10px] text-slate-400">
                                <span>
                                    <i class="bi bi-calendar-event me-1"></i>
                                    Starts: ${dateTime(poll.start_at)}
                                </span>

                                <span>
                                    <i class="bi bi-calendar-x me-1"></i>
                                    Ends: ${dateTime(poll.end_at)}
                                </span>
                            </div>
                        </div>

                        <span class="inline-flex shrink-0 rounded-md bg-emerald-50 px-2 py-1 text-[10px] font-semibold text-emerald-700">
                            Active
                        </span>
                    </div>

                    ${
                        poll.description
                            ?`
                                <p class="mb-4 whitespace-pre-line text-xs leading-5 text-slate-500">
                                    ${escapeValue(poll.description)}
                                </p>
                            `
                            :''
                    }

                    <div class="space-y-2">
                        ${options.map((option,index)=>{
                            const selected=
                                voted&&
                                Number(option.id)===selectedOptionId;

                            return`
                                <div class="flex items-center gap-3 rounded-md border ${
                                    selected
                                        ?'border-indigo-200 bg-indigo-50'
                                        :'border-slate-200 bg-slate-50/50'
                                } px-3 py-2.5">

                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full ${
                                        selected
                                            ?'bg-indigo-600 text-white'
                                            :'bg-white text-slate-400'
                                    } text-[10px] font-bold">

                                        ${
                                            selected
                                                ?'<i class="bi bi-check-lg"></i>'
                                                :index+1
                                        }
                                    </span>

                                    <span class="min-w-0 flex-1 text-xs font-medium ${
                                        selected
                                            ?'text-indigo-700'
                                            :'text-slate-600'
                                    }">
                                        ${escapeValue(option.option_text??'Option')}
                                    </span>

                                    ${
                                        selected
                                            ?`
                                                <span class="shrink-0 text-[10px] font-semibold text-indigo-600">
                                                    Your Vote
                                                </span>
                                            `
                                            :''
                                    }
                                </div>
                            `;
                        }).join('')}
                    </div>

                    ${
                        voted
                            ?`
                                <div class="mt-4 flex items-center gap-2 rounded-md border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span>Your vote has been submitted successfully.</span>
                                </div>
                            `
                            :`
                                <div class="mt-4 flex items-center gap-2 rounded-md border border-indigo-100 bg-indigo-50 px-3 py-2 text-xs text-indigo-700">
                                    <i class="bi bi-info-circle"></i>
                                    <span>You have not voted in this poll yet.</span>
                                </div>
                            `
                    }
                </div>

                <div class="flex items-center justify-between gap-2 border-t border-slate-100 bg-slate-50/50 px-5 py-3">
                    <button
                        type="button"
                        onclick="openHistoryModal()"
                        class="h-8 rounded-md border border-slate-200 bg-white px-3 text-[11px] font-semibold text-slate-600 transition hover:bg-slate-50">
                        <i class="bi bi-clock-history me-1"></i>
                        History
                    </button>

                    ${
                        !voted
                            ?`
                                <button
                                    type="button"
                                    onclick="openVoteModal(${Number(poll.id)})"
                                    class="h-8 rounded-md bg-indigo-600 px-3 text-[11px] font-semibold text-white transition hover:bg-indigo-700">
                                    <i class="bi bi-check2-square me-1"></i>
                                    Vote Now
                                </button>
                            `
                            :`
                                <span class="inline-flex h-8 items-center gap-1.5 rounded-md bg-emerald-50 px-3 text-[11px] font-semibold text-emerald-700">
                                    <i class="bi bi-check-circle"></i>
                                    Voted
                                </span>
                            `
                    }
                </div>
            </div>
        `;
    }).join('');
}

window.openVoteModal=function(pollId){
    const poll=memberPolls.find(
        item=>Number(item.id)===Number(pollId)
    );

    if(!poll){
        Toast.error('Poll not found.');
        return;
    }

    if(poll.has_voted){
        Toast.error('You have already voted in this poll.');
        return;
    }

    selectedPoll=poll;
    selectedOptionId=null;

    document.getElementById('votePollTitle').textContent=
        poll.title??'Poll';

    document.getElementById('votePollDescription').textContent=
        poll.description??'';

    document.getElementById('voteError').classList.add('hidden');
    document.getElementById('voteError').textContent='';

    document.getElementById('voteOptions').innerHTML=
        (poll.options??[]).map((option,index)=>`
            <label
                class="group flex cursor-pointer items-center gap-3 rounded-md border border-slate-200 bg-white px-3 py-3 transition hover:border-indigo-200 hover:bg-indigo-50/40">

                <input
                    type="radio"
                    name="pollOption"
                    value="${Number(option.id)}"
                    onchange="selectPollOption(${Number(option.id)})"
                    class="h-4 w-4 cursor-pointer border-slate-300 text-indigo-600 focus:ring-indigo-500">

                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-500">
                    ${index+1}
                </span>

                <span class="text-xs font-medium text-slate-700">
                    ${escapeValue(option.option_text??'Option')}
                </span>
            </label>
        `).join('');

    AdminUI.openModal('voteModal');
};

window.selectPollOption=function(id){
    selectedOptionId=Number(id);
};

window.closeVoteModal=function(){
    selectedPoll=null;
    selectedOptionId=null;
    AdminUI.closeModal('voteModal');
};

window.submitVote=async function(){
    if(!selectedPoll){
        Toast.error('Poll not found.');
        return;
    }

    if(!selectedOptionId){
        showVoteError('Please select an option.');
        return;
    }

    const button=document.getElementById('submitVoteButton');
    const original=button.innerHTML;

    button.disabled=true;
    button.innerHTML=`
        <i class="bi bi-arrow-repeat me-1 animate-spin"></i>
        Submitting...
    `;

    document.getElementById('voteError').classList.add('hidden');

    try{
        const response=await api(
            `/api/polls/${selectedPoll.id}/vote`,
            {
                method:'POST',
                body:JSON.stringify({
                    poll_option_id:selectedOptionId
                })
            }
        );

        closeVoteModal();

        Toast.success(
            response.message??'Vote submitted successfully.'
        );

        await loadPolls();
    }catch(error){
        showVoteError(
            AdminUI.extractError(error)
        );
    }finally{
        button.disabled=false;
        button.innerHTML=original;
    }
};

function showVoteError(message){
    const error=document.getElementById('voteError');

    error.textContent=message;
    error.classList.remove('hidden');
}

window.openHistoryModal=function(){
    const body=document.getElementById('historyBody');

    if(!votingHistory.length){
        body.innerHTML=`
            <div class="py-10 text-center">
                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <i class="bi bi-clock-history"></i>
                </div>

                <p class="mt-3 text-sm font-semibold text-slate-600">
                    No voting history
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    You have not voted in any polls yet.
                </p>
            </div>
        `;

        AdminUI.openModal('historyModal');
        return;
    }

    body.innerHTML=`
        <div class="overflow-hidden rounded-md border border-slate-200">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px] text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                                Poll
                            </th>

                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                                Your Vote
                            </th>

                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                                Voted At
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        ${votingHistory.map(item=>`
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="px-4 py-3 text-xs font-medium text-slate-700">
                                    ${escapeValue(item.poll_title??'—')}
                                </td>

                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-md bg-indigo-50 px-2 py-1 text-[10px] font-semibold text-indigo-700">
                                        ${escapeValue(item.option_text??'—')}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-xs text-slate-500">
                                    ${dateTime(item.created_at)}
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;

    AdminUI.openModal('historyModal');
};

window.closeHistoryModal=function(){
    AdminUI.closeModal('historyModal');
};

function dateTime(value){
    if(!value)return '—';

    const date=new Date(value);

    if(Number.isNaN(date.getTime())){
        return '—';
    }

    return date.toLocaleString('en-GB',{
        day:'2-digit',
        month:'short',
        year:'numeric',
        hour:'2-digit',
        minute:'2-digit'
    });
}

function escapeValue(value){
    return AdminUI.escapeHtml(
        String(value??'')
    );
}

async function initPollPage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(initPollPage,50);
        return;
    }

    await loadPolls();
}

if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initPollPage
    );
}else{
    initPollPage();
}
</script>
@endpush