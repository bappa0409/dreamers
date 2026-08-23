@extends('layouts.member')

@section('title','Committee')
@section('page-title','Committee')

@section('content')
<div class="space-y-5">

{{-- Header --}}
<div class="flex flex-col gap-4 rounded-lg border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
<div class="flex items-center gap-4">
<div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-sm">
<i class="bi bi-person-badge"></i>
</div>

<div>
<h1 class="text-lg font-bold tracking-tight text-slate-800">Association Committee</h1>
<p class="mt-0.5 text-xs text-slate-500">View the current committee, tenure and association office bearers.</p>
</div>
</div>

<button id="refreshCommitteeButton" type="button" onclick="loadCommittee()" class="inline-flex h-9 w-fit items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 disabled:cursor-not-allowed disabled:opacity-60">
<i id="refreshCommitteeIcon" class="bi bi-arrow-clockwise"></i>
<span>Refresh</span>
</button>
</div>

{{-- Information --}}
<div class="flex gap-3 rounded-lg border border-indigo-200 bg-indigo-50/50 p-4">
<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
<i class="bi bi-info-circle"></i>
</div>

<div>
<p class="text-xs font-semibold text-indigo-800">Committee Information</p>
<p class="mt-0.5 text-[11px] leading-5 text-indigo-700">
This page displays the association's currently active committee term and office bearers. Committee appointments and election results are managed by the administration.
</p>
</div>
</div>

{{-- Summary --}}
<div class="grid grid-cols-2 gap-3 xl:grid-cols-4">

<div class="rounded-lg border border-slate-200 bg-white p-4">
<div class="flex items-start justify-between gap-3">
<div>
<p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Active Committees</p>
<p id="summaryCommittees" class="mt-2 text-xl font-bold text-slate-800">0</p>
</div>

<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
<i class="bi bi-diagram-3"></i>
</div>
</div>
</div>

<div class="rounded-lg border border-emerald-200 bg-emerald-50/40 p-4">
<div class="flex items-start justify-between gap-3">
<div>
<p class="text-[10px] font-medium uppercase tracking-wide text-emerald-600">Office Bearers</p>
<p id="summaryMembers" class="mt-2 text-xl font-bold text-emerald-700">0</p>
</div>

<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
<i class="bi bi-people"></i>
</div>
</div>
</div>

<div class="rounded-lg border border-sky-200 bg-sky-50/40 p-4">
<div class="flex items-start justify-between gap-3">
<div>
<p class="text-[10px] font-medium uppercase tracking-wide text-sky-600">Current Term</p>
<p id="summaryTerm" class="mt-2 truncate text-sm font-bold text-sky-700">—</p>
</div>

<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
<i class="bi bi-calendar-check"></i>
</div>
</div>
</div>

<div class="rounded-lg border border-violet-200 bg-violet-50/40 p-4">
<div class="flex items-start justify-between gap-3">
<div>
<p class="text-[10px] font-medium uppercase tracking-wide text-violet-600">Term Ends</p>
<p id="summaryEndDate" class="mt-2 text-sm font-bold text-violet-700">—</p>
</div>

<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-violet-100 text-violet-600">
<i class="bi bi-calendar-event"></i>
</div>
</div>
</div>

</div>

{{-- Committee Content --}}
<div id="committeeContent">

<div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
<div class="border-b border-slate-200 px-5 py-4">
<div class="flex items-center gap-3">
<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
<i class="bi bi-person-badge"></i>
</div>

<div>
<h2 class="text-sm font-semibold text-slate-800">Current Committee</h2>
<p class="text-[11px] text-slate-400">Loading active committee information...</p>
</div>
</div>
</div>

<div class="py-14 text-center">
<div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>
<p class="mt-3 text-xs text-slate-400">Loading committee...</p>
</div>
</div>

</div>

</div>
@endsection

@push('scripts')
<script>
let committeeTerms=[];

const $=id=>document.getElementById(id);

async function loadCommittee(){
const root=$('committeeContent');
const button=$('refreshCommitteeButton');
const icon=$('refreshCommitteeIcon');

if(button){
button.disabled=true;
}

if(icon){
icon.classList.add('animate-spin');
}

root.innerHTML=loadingState();

try{
const response=await api('/api/committees/current');

committeeTerms=Array.isArray(response.data)
?response.data
:[];

renderSummary();
renderCommittee();

}catch(error){
committeeTerms=[];

renderSummary();

root.innerHTML=`
<div class="overflow-hidden rounded-lg border border-red-200 bg-white">

<div class="border-b border-red-100 bg-red-50/50 px-5 py-4">
<div class="flex items-center gap-3">

<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-100 text-red-600">
<i class="bi bi-exclamation-circle"></i>
</div>

<div>
<h2 class="text-sm font-semibold text-red-700">Unable to Load Committee</h2>
<p class="text-[11px] text-red-500">Committee information could not be loaded.</p>
</div>

</div>
</div>

<div class="py-12 text-center">

<p class="text-xs text-red-500">
${escapeHtml(AdminUI.extractError(error))}
</p>

<button type="button" onclick="loadCommittee()" class="mt-4 inline-flex h-9 items-center gap-2 rounded-md border border-red-200 bg-white px-3 text-xs font-semibold text-red-600 transition hover:bg-red-50">
<i class="bi bi-arrow-clockwise"></i>
Try Again
</button>

</div>
</div>`;
}finally{
if(button){
button.disabled=false;
}

if(icon){
icon.classList.remove('animate-spin');
}
}
}

function renderSummary(){
const memberCount=committeeTerms.reduce(
(total,term)=>
total+(term.active_members??[]).length,
0
);

$('summaryCommittees').textContent=
committeeTerms.length;

$('summaryMembers').textContent=
memberCount;

const primaryTerm=committeeTerms[0];

$('summaryTerm').textContent=
primaryTerm?.name??'—';

$('summaryEndDate').textContent=
primaryTerm?.end_date
?dateText(primaryTerm.end_date)
:'—';
}

function renderCommittee(){
const root=$('committeeContent');

if(!committeeTerms.length){
root.innerHTML=`
<div class="overflow-hidden rounded-lg border border-slate-200 bg-white">

<div class="border-b border-slate-200 px-5 py-4">

<div class="flex items-center gap-3">

<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
<i class="bi bi-person-badge"></i>
</div>

<div>
<h2 class="text-sm font-semibold text-slate-800">
Current Committee
</h2>

<p class="text-[11px] text-slate-400">
Association committee information
</p>
</div>

</div>
</div>

<div class="py-14 text-center">

<div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 text-indigo-500">
<i class="bi bi-people text-xl"></i>
</div>

<p class="mt-4 text-sm font-semibold text-slate-700">
No Active Committee
</p>

<p class="mx-auto mt-1 max-w-sm text-xs leading-5 text-slate-400">
There is currently no active association committee or committee term.
</p>

</div>
</div>`;

return;
}

root.innerHTML=`
<div class="space-y-5">
${committeeTerms.map(
(term,index)=>renderTerm(term,index)
).join('')}
</div>`;
}

function renderTerm(term,index){
const members=term.active_members??[];
const committee=term.committee??{};
const progress=calculateTermProgress(
term.start_date,
term.end_date
);

return`
<section class="overflow-hidden rounded-lg border border-slate-200 bg-white">

{{-- Committee Header --}}
<div class="border-b border-slate-200 bg-slate-50/50 px-5 py-4">

<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

<div class="flex items-start gap-3">

<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
<i class="bi bi-diagram-3"></i>
</div>

<div>

<div class="flex flex-wrap items-center gap-2">

<h2 class="text-base font-bold text-slate-800">
${escapeHtml(
committee.name||
'Association Committee'
)}
</h2>

<span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-[9px] font-semibold uppercase text-emerald-700">
<span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
Active
</span>

</div>

<p class="mt-1 text-[11px] text-slate-500">
${escapeHtml(term.name??'Current Term')}
</p>

${committee.description?`
<p class="mt-1 max-w-2xl text-[11px] leading-5 text-slate-400">
${escapeHtml(committee.description)}
</p>
`:''}

</div>
</div>

<div class="shrink-0 text-left sm:text-right">

<p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400">
Term Duration
</p>

<p class="mt-1 text-xs font-semibold text-slate-700">
${dateText(term.start_date)}
<span class="mx-1 text-slate-300">—</span>
${dateText(term.end_date)}
</p>

</div>
</div>

${renderTermProgress(progress)}

</div>

{{-- Committee Overview --}}
<div class="border-b border-slate-100 px-5 py-4">

<div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

${overviewBox(
'Office Bearers',
String(members.length),
'bi-people',
'indigo'
)}

${overviewBox(
'Term Started',
dateText(term.start_date),
'bi-calendar-check',
'sky'
)}

${overviewBox(
'Term Ends',
dateText(term.end_date),
'bi-calendar-event',
'violet'
)}

${overviewBox(
'Progress',
progress.label,
'bi-hourglass-split',
progress.status==='completed'
?'emerald'
:progress.status==='upcoming'
?'amber'
:'indigo'
)}

</div>
</div>

{{-- Office Bearers --}}
<div>

<div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">

<div>
<h3 class="text-xs font-semibold text-slate-700">
Office Bearers
</h3>

<p class="mt-0.5 text-[10px] text-slate-400">
Current members serving in this committee
</p>
</div>

<span class="inline-flex min-w-7 items-center justify-center rounded-full bg-indigo-50 px-2 py-1 text-[10px] font-bold text-indigo-600">
${members.length}
</span>

</div>

${members.length
?renderMembers(members)
:renderEmptyMembers()}

</div>

</section>`;
}

function renderTermProgress(progress){
return`
<div class="mt-4">

<div class="mb-1.5 flex items-center justify-between">

<span class="text-[10px] font-medium text-slate-400">
Term Progress
</span>

<span class="text-[10px] font-semibold ${progress.textClass}">
${escapeHtml(progress.label)}
</span>

</div>

<div class="h-1.5 overflow-hidden rounded-full bg-slate-200">

<div class="h-full rounded-full ${progress.barClass} transition-all duration-500"
style="width:${progress.percent}%">
</div>

</div>

</div>`;
}

function renderMembers(members){
const sorted=[...members].sort(
(a,b)=>
Number(
a.position?.sort_order??999
)-
Number(
b.position?.sort_order??999
)
);

return`
<div class="grid gap-3 p-4 sm:grid-cols-2 xl:grid-cols-3">

${sorted.map(
(member,index)=>renderMemberCard(member,index)
).join('')}

</div>`;
}

function renderMemberCard(item,index){
const member=item.member??{};
const user=member.user??{};
const position=item.position??{};

const name=user.name??'Committee Member';
const initial=String(name)
.trim()
.charAt(0)
.toUpperCase()||'?';

const photo=member.profile_photo_url??null;

const isTopPosition=
Number(position.sort_order??999)<=3||
['president','chairman','chairperson','general secretary','secretary']
.includes(
String(position.name??'').toLowerCase()
);

return`
<article class="group overflow-hidden rounded-lg border ${isTopPosition?'border-indigo-200':'border-slate-200'} bg-white transition hover:border-indigo-300 hover:shadow-sm">

${isTopPosition?`
<div class="h-1 bg-gradient-to-r from-indigo-500 to-violet-500"></div>
`:''}

<div class="p-4">

<div class="flex items-start gap-3">

<div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full ${photo?'bg-slate-100':'bg-gradient-to-br from-indigo-100 to-violet-100 text-indigo-600'} text-sm font-bold ring-2 ring-white shadow-sm">

${photo
?`<img
src="${escapeAttribute(photo)}"
alt="${escapeAttribute(name)}"
class="h-full w-full object-cover"
onerror="this.style.display='none';this.parentElement.innerHTML='${escapeAttribute(initial)}';"
>`
:escapeHtml(initial)
}

</div>

<div class="min-w-0 flex-1">

<p class="truncate text-sm font-bold text-slate-800">
${escapeHtml(name)}
</p>

<p class="mt-0.5 truncate text-[11px] font-semibold text-indigo-600">
${escapeHtml(
position.name||
'Committee Member'
)}
</p>

${member.member_code?`
<p class="mt-1 truncate font-mono text-[9px] font-medium text-slate-400">
${escapeHtml(member.member_code)}
</p>
`:''}

</div>

${isTopPosition?`
<div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-500" title="Key Office Bearer">
<i class="bi bi-star-fill text-[10px]"></i>
</div>
`:''}

</div>

<div class="mt-4 grid grid-cols-2 gap-2">

${miniInfo(
'Method',
appointmentLabel(item.appointment_method)
)}

${miniInfo(
'Status',
statusLabel(item.status)
)}

</div>

</div>

</article>`;
}

function renderEmptyMembers(){
return`
<div class="py-12 text-center">

<div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
<i class="bi bi-person-x"></i>
</div>

<p class="mt-3 text-sm font-semibold text-slate-600">
No Office Bearers
</p>

<p class="mt-1 text-xs text-slate-400">
No active members have been assigned to this committee term.
</p>

</div>`;
}

function overviewBox(label,value,icon,tone='slate'){
const colors={
slate:'bg-slate-50 text-slate-500',
indigo:'bg-indigo-50 text-indigo-600',
sky:'bg-sky-50 text-sky-600',
violet:'bg-violet-50 text-violet-600',
emerald:'bg-emerald-50 text-emerald-600',
amber:'bg-amber-50 text-amber-600'
};

return`
<div class="rounded-lg bg-slate-50 p-3">

<div class="flex items-start justify-between gap-2">

<div class="min-w-0">

<p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400">
${escapeHtml(label)}
</p>

<p class="mt-1.5 truncate text-xs font-bold text-slate-700">
${escapeHtml(value)}
</p>

</div>

<div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md ${colors[tone]??colors.slate}">
<i class="bi ${icon} text-xs"></i>
</div>

</div>
</div>`;
}

function miniInfo(label,value){
return`
<div class="rounded-md bg-slate-50 px-2.5 py-2">

<p class="text-[8px] font-semibold uppercase tracking-wide text-slate-400">
${escapeHtml(label)}
</p>

<p class="mt-1 truncate text-[10px] font-semibold text-slate-600">
${escapeHtml(value)}
</p>

</div>`;
}

function calculateTermProgress(startValue,endValue){
if(!startValue||!endValue){
return{
percent:0,
label:'Unknown',
status:'unknown',
barClass:'bg-slate-400',
textClass:'text-slate-500'
};
}

const start=new Date(
String(startValue).substring(0,10)+'T00:00:00'
);

const end=new Date(
String(endValue).substring(0,10)+'T23:59:59'
);

const now=new Date();

if(
Number.isNaN(start.getTime())||
Number.isNaN(end.getTime())
){
return{
percent:0,
label:'Unknown',
status:'unknown',
barClass:'bg-slate-400',
textClass:'text-slate-500'
};
}

if(now<start){
return{
percent:0,
label:'Upcoming',
status:'upcoming',
barClass:'bg-amber-500',
textClass:'text-amber-600'
};
}

if(now>end){
return{
percent:100,
label:'Completed',
status:'completed',
barClass:'bg-emerald-500',
textClass:'text-emerald-600'
};
}

const duration=end-start;
const elapsed=now-start;

const percent=Math.min(
100,
Math.max(
0,
Math.round(
(elapsed/duration)*100
)
)
);

return{
percent,
label:`${percent}% Complete`,
status:'active',
barClass:'bg-indigo-500',
textClass:'text-indigo-600'
};
}

function appointmentLabel(value){
return{
appointed:'Appointed',
elected:'Elected',
replacement:'Replacement'
}[value]??titleCase(value??'Assigned');
}

function statusLabel(value){
return{
active:'Active',
completed:'Completed',
resigned:'Resigned',
removed:'Removed'
}[value]??titleCase(value??'Active');
}

function titleCase(value){
return String(value??'')
.replaceAll('_',' ')
.replace(/\b\w/g,char=>char.toUpperCase());
}

function dateText(value){
if(!value)return'—';

const raw=String(value).substring(0,10);

const parsed=new Date(
raw+'T00:00:00'
);

if(Number.isNaN(parsed.getTime())){
return raw;
}

return parsed.toLocaleDateString(
'en-GB',
{
day:'2-digit',
month:'short',
year:'numeric'
}
);
}

function escapeHtml(value){
const div=document.createElement('div');
div.textContent=String(value??'');
return div.innerHTML;
}

function escapeAttribute(value){
return String(value??'')
.replaceAll('&','&amp;')
.replaceAll('"','&quot;')
.replaceAll("'",'&#039;')
.replaceAll('<','&lt;')
.replaceAll('>','&gt;');
}

function loadingState(){
return`
<div class="overflow-hidden rounded-lg border border-slate-200 bg-white">

<div class="border-b border-slate-200 px-5 py-4">

<div class="flex items-center gap-3">

<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
<i class="bi bi-person-badge"></i>
</div>

<div>
<h2 class="text-sm font-semibold text-slate-800">
Current Committee
</h2>

<p class="text-[11px] text-slate-400">
Loading active committee information...
</p>
</div>

</div>
</div>

<div class="py-14 text-center">

<div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>

<p class="mt-3 text-xs text-slate-400">
Loading committee...
</p>

</div>
</div>`;
}

async function init(){
if(
typeof AdminUI==='undefined'||
typeof api==='undefined'
){
setTimeout(
init,
50
);
return;
}

await loadCommittee();
}

init();
</script>
@endpush