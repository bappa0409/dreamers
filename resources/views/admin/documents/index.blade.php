@extends('layouts.admin')

@section('title','Documents')
@section('page_title','Documents')

@section('content')

<div class="space-y-3">

    {{-- =========================================================
    HEADER
    ========================================================== --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-folder2-open"></i>
            </div>

            <div>
                <h1 class="text-base font-bold text-slate-800">
                    Document Management
                </h1>

                <p class=" text-xs 2xl:text-sm text-slate-500">
                    Upload, organize and securely share association documents.
                </p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Document.create'))
            <button
                type="button"
                onclick="openDocumentModal()"
                class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs 2xl:text-sm font-semibold text-white transition hover:bg-indigo-700"
            >
                <i class="bi bi-cloud-arrow-up"></i>
                Upload Document
            </button>
        @endif
    </div>


    {{-- =========================================================
    SEARCH / FILTERS
    ========================================================== --}}
    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-base"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700">
                        Search Documents
                    </p>

                    <p class="hidden text-[11px] text-slate-500 sm:block">
                        Search by title, filename, category or description
                    </p>
                </div>
            </div>

            <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center lg:w-auto lg:gap-0">

                <div class="relative w-full sm:min-w-[230px] lg:w-80">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-500"></i>

                    <input
                        id="searchInput"
                        type="text"
                        placeholder="Search documents..."
                        class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-xs 2xl:text-sm text-slate-700 outline-none placeholder:text-slate-500 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 lg:rounded-r-none"
                    >
                </div>

                <select
                    id="categoryFilter"
                    class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs 2xl:text-sm font-medium text-slate-600 outline-none focus:border-indigo-400 lg:w-[170px] lg:rounded-none lg:border-l-0"
                >
                    <option value="">All Categories</option>
                </select>

                <select
                    id="extensionFilter"
                    class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs 2xl:text-sm font-medium text-slate-600 outline-none focus:border-indigo-400 lg:w-[130px] lg:rounded-none lg:border-l-0"
                >
                    <option value="">All Types</option>
                </select>

                <button
                    type="button"
                    onclick="clearFilters()"
                    class="inline-flex h-9 shrink-0 cursor-pointer items-center justify-center gap-1.5 whitespace-nowrap rounded-md border border-slate-300 bg-slate-50 px-3 text-xs 2xl:text-sm font-semibold text-slate-600 transition hover:bg-slate-100 lg:rounded-l-none lg:border-l-0"
                >
                    <i class="bi bi-x-lg text-[10px]"></i>
                    Clear
                </button>

            </div>

        </div>
    </div>


    {{-- =========================================================
    DOCUMENT GRID
    ========================================================== --}}
    <div
        id="documentGrid"
        class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3"
    >
        <div class="col-span-full">
            <div class="rounded-md border border-slate-200 bg-white p-10 text-center text-base text-slate-500">
                Loading documents...
            </div>
        </div>
    </div>


    {{-- =========================================================
    PAGINATION
    ========================================================== --}}
    <div id="paginationContainer"></div>

</div>


{{-- =============================================================
DOCUMENT MODAL
============================================================= --}}
<div
    id="documentModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5"
>
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">

        {{-- Header --}}
        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-5">

            <div class="flex min-w-0 items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i
                        id="modalIcon"
                        class="bi bi-cloud-arrow-up"
                    ></i>
                </div>

                <div class="min-w-0">
                    <h2
                        id="documentModalTitle"
                        class="text-sm font-semibold text-slate-800"
                    >
                        Upload Document
                    </h2>

                    <p
                        id="documentModalDescription"
                        class=" text-xs 2xl:text-sm text-slate-500"
                    >
                        Upload a new association document.
                    </p>
                </div>
            </div>

            <button
                type="button"
                onclick="closeDocumentModal()"
                class="app-modal-close"
            >
                <i class="bi bi-x-lg"></i>
            </button>

        </div>


        {{-- Form --}}
        <form
            id="documentForm"
            class="flex min-h-0 flex-1 flex-col"
         novalidate data-js-validation="1">
            <div class="space-y-4 overflow-y-auto p-5">

                {{-- Title --}}
                <div>
                    <label class="form-label">
                        Title
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        id="title"
                        type="text"
                        maxlength="255"
                        class="app-input"
                        placeholder="Document title"
                    >
                </div>


                {{-- Category + Visibility --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                    <div>
                        <label class="form-label">
                            Category
                        </label>

                        <input
                            id="category"
                            type="text"
                            maxlength="100"
                            placeholder="Meeting, Finance, Legal..."
                            class="app-input"
                        >
                    </div>


                    <div>
                        <label class="form-label">
                            Visibility
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="visibility"
                            class="app-input"
                        >
                            <option value="members">Members</option>
                            <option value="internal">Internal</option>
                            <option value="private">Private</option>
                            <option value="public">Public</option>
                        </select>
                    </div>

                </div>


                {{-- Description --}}
                <div>
                    <label class="form-label">
                        Description
                    </label>

                    <textarea
                        id="description"
                        rows="3"
                        maxlength="3000"
                        class="app-input resize-none"
                        placeholder="Optional document description"
                    ></textarea>
                </div>


                {{-- File --}}
                <div>
                    <label class="form-label">
                        File

                        <span
                            id="fileRequiredMark"
                            class="text-red-500"
                        >
                            *
                        </span>
                    </label>

                    <label
                        for="file"
                        class="flex cursor-pointer flex-col items-center justify-center rounded-md border border-dashed border-slate-300 bg-slate-50 px-5 py-6 text-center transition hover:border-indigo-300 hover:bg-indigo-50/30"
                    >
                        <i class="bi bi-cloud-arrow-up text-2xl text-indigo-500"></i>

                        <span
                            id="selectedFileName"
                            class="mt-2 max-w-full truncate text-sm font-semibold text-slate-600"
                        >
                            Choose a file
                        </span>

                        <span
                            id="fileHint"
                            class="mt-1 text-[11px] text-slate-500"
                        >
                            PDF, Office, image, ZIP or text file. Maximum 20 MB.
                        </span>
                    </label>

                    <input
                        id="file"
                        type="file"
                        class="hidden"
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.webp,.zip"
                    >
                </div>


                {{-- Active --}}
                <label class="flex cursor-pointer items-center gap-3 rounded-md border border-slate-200 bg-slate-50/50 p-3">

                    <input
                        id="isActive"
                        type="checkbox"
                        checked
                        class="h-4 w-4 rounded border-slate-300 text-indigo-600"
                    >

                    <div>
                        <p class="text-sm font-semibold text-slate-700">
                            Active Document
                        </p>

                        <p class="mt-0.5 text-[11px] text-slate-500">
                            Inactive documents remain stored but are hidden from normal members.
                        </p>
                    </div>

                </label>


                {{-- Error --}}
                <div
                    id="formError"
                    class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"
                ></div>

            </div>


            {{-- Footer --}}
            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">

                <button
                    type="button"
                    onclick="closeDocumentModal()"
                    class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                >
                    Cancel
                </button>

                <button
                    id="saveButton"
                    type="submit"
                    class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-xs 2xl:text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    Upload Document
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

@endsection


@push('scripts')

<script>
let documents=[];
let editingDocument=null;
let currentPage=1;
let lastPage=1;
let totalDocuments=0;

const canUpdate=@json(
    auth()->user()->hasPermission('Document.update')
);

const canDelete=@json(
    auth()->user()->hasPermission('Document.delete')
);

const elements={
    grid:document.getElementById('documentGrid'),
    search:document.getElementById('searchInput'),
    categoryFilter:document.getElementById('categoryFilter'),
    extensionFilter:document.getElementById('extensionFilter'),
    form:document.getElementById('documentForm'),
    title:document.getElementById('title'),
    category:document.getElementById('category'),
    description:document.getElementById('description'),
    visibility:document.getElementById('visibility'),
    isActive:document.getElementById('isActive'),
    file:document.getElementById('file'),
    fileHint:document.getElementById('fileHint'),
    selectedFileName:document.getElementById('selectedFileName'),
    saveButton:document.getElementById('saveButton'),
    modalTitle:document.getElementById('documentModalTitle'),
    modalDescription:document.getElementById('documentModalDescription'),
    modalIcon:document.getElementById('modalIcon'),
    fileRequiredMark:document.getElementById('fileRequiredMark')
};


/*
|--------------------------------------------------------------------------
| Loading State
|--------------------------------------------------------------------------
*/

function documentLoadingState(){
    elements.grid.innerHTML=`
        <div class="col-span-full">
            ${AdminUI.loadingState(
                'Loading documents...'
            )}
        </div>
    `;
}


/*
|--------------------------------------------------------------------------
| Empty State
|--------------------------------------------------------------------------
*/

function documentEmptyState(){
    elements.grid.innerHTML=`
        <div class="col-span-full">
            ${AdminUI.emptyState(
                'No documents found.'
            )}
        </div>
    `;
}


/*
|--------------------------------------------------------------------------
| Error State
|--------------------------------------------------------------------------
*/

function documentErrorState(error){
    elements.grid.innerHTML=`
        <div class="col-span-full">
            <div class="rounded-md border border-red-200 bg-red-50 p-8 text-center text-base text-red-600">
                ${AdminUI.escapeHtml(
                    AdminUI.extractError(error)
                )}
            </div>
        </div>
    `;
}


/*
|--------------------------------------------------------------------------
| Load Documents
|--------------------------------------------------------------------------
*/

async function loadDocuments(page=1){
    currentPage=page;

    documentLoadingState();

    const query=AdminUI.query({
        search:elements.search.value.trim(),
        category:elements.categoryFilter.value,
        extension:elements.extensionFilter.value,
        page
    });

    try{
        const response=await api(
            `/api/documents?${query}`
        );

        const paginator=response.data??{};

        documents=Array.isArray(paginator.data)
            ?paginator.data
            :[];

        currentPage=Number(
            paginator.current_page??1
        );

        lastPage=Number(
            paginator.last_page??1
        );

        totalDocuments=Number(
            paginator.total??documents.length
        );

        renderDocuments();

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total:totalDocuments,
            onPageChange:loadDocuments
        });

    }catch(error){
        documentErrorState(error);

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage:1,
            lastPage:1,
            total:0,
            onPageChange:loadDocuments
        });
    }
}


/*
|--------------------------------------------------------------------------
| Load Filters
|--------------------------------------------------------------------------
*/

async function loadFilters(){
    try{
        const response=await api(
            '/api/documents/filters'
        );

        const data=response.data??{};

        populateFilter(
            elements.categoryFilter,
            data.categories??[],
            'All Categories',
            false
        );

        populateFilter(
            elements.extensionFilter,
            data.extensions??[],
            'All Types',
            true
        );

    }catch(error){
        console.error(
            'Document filters load failed:',
            error
        );
    }
}


/*
|--------------------------------------------------------------------------
| Populate Filter
|--------------------------------------------------------------------------
*/

function populateFilter(
    select,
    items,
    placeholder,
    uppercase=false
){
    const selected=select.value;

    select.innerHTML=`
        <option value="">
            ${placeholder}
        </option>
    `;

    items.forEach(item=>{
        const option=
            document.createElement('option');

        option.value=item;

        option.textContent=uppercase
            ?String(item).toUpperCase()
            :item;

        select.appendChild(option);
    });

    if(
        [...select.options].some(
            option=>option.value===selected
        )
    ){
        select.value=selected;
    }
}


/*
|--------------------------------------------------------------------------
| Render Documents
|--------------------------------------------------------------------------
*/

function renderDocuments(){
    if(!documents.length){
        documentEmptyState();
        return;
    }

    elements.grid.innerHTML=
        documents.map(item=>`
            <article class="flex min-w-0 flex-col overflow-hidden rounded-md border border-slate-200 bg-white transition hover:border-slate-300">

                {{-- Header --}}
                <div class="flex min-w-0 items-start gap-3 p-4">

                    ${filePreview(item)}

                    <div class="min-w-0 flex-1">

                        <h3
                            class="truncate text-base font-bold text-slate-800"
                            title="${AdminUI.escapeHtml(item.title??'')}"
                        >
                            ${AdminUI.escapeHtml(
                                item.title??
                                'Untitled'
                            )}
                        </h3>

                        <p
                            class="mt-1 truncate text-[11px] text-slate-500"
                            title="${AdminUI.escapeHtml(item.original_name??'')}"
                        >
                            ${AdminUI.escapeHtml(
                                item.original_name??
                                ''
                            )}
                        </p>

                    </div>

                    <div class="shrink-0">
                        ${AdminUI.visibilityBadge(
                            item.visibility
                        )}
                    </div>

                </div>


                {{-- Content --}}
                <div class="min-w-0 flex-1 px-4">

                    <div class="flex min-w-0 flex-wrap items-center gap-1.5">

                        ${
                            item.category
                                ?`
                                    <span
                                        class="max-w-[160px] truncate rounded-md bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-600"
                                        title="${AdminUI.escapeHtml(item.category)}"
                                    >
                                        ${AdminUI.escapeHtml(
                                            item.category
                                        )}
                                    </span>
                                `
                                :''
                        }


                        <span class="rounded-md bg-sky-50 px-2 py-1 text-[10px] font-semibold uppercase text-sky-700">
                            ${AdminUI.escapeHtml(
                                item.extension??
                                'file'
                            )}
                        </span>


                        <span class="rounded-md bg-slate-50 px-2 py-1 text-[10px] text-slate-500">
                            ${AdminUI.formatBytes(
                                item.size
                            )}
                        </span>


                        ${
                            !item.is_active
                                ?`
                                    <span class="rounded-md bg-red-50 px-2 py-1 text-[10px] font-semibold text-red-600">
                                        Inactive
                                    </span>
                                `
                                :''
                        }

                    </div>


                    ${
                        item.description
                            ?`
                                <p class="mt-3 line-clamp-2 break-words text-sm leading-5 text-slate-500">
                                    ${AdminUI.escapeHtml(
                                        item.description
                                    )}
                                </p>
                            `
                            :`
                                <p class="mt-3 text-sm italic text-slate-300">
                                    No description
                                </p>
                            `
                    }

                </div>


                {{-- Footer --}}
                <div class="mt-4 flex min-w-0 items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/40 px-4 py-3">

                    <div class="min-w-0">
                        <p class="truncate text-[10px] text-slate-500">
                            ${
                                item.uploader?.name
                                    ?`By ${AdminUI.escapeHtml(item.uploader.name)}`
                                    :'Uploader unavailable'
                            }
                        </p>
                    </div>


                    <div class="flex shrink-0 items-center gap-1">

                        <button
                            type="button"
                            onclick="downloadDocument(${item.id})"
                            title="Download"
                            class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100"
                        >
                            <i class="bi bi-download text-sm"></i>
                        </button>


                        ${
                            canUpdate
                                ?`
                                    <button
                                        type="button"
                                        onclick="editDocument(${item.id})"
                                        title="Edit"
                                        class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100"
                                    >
                                        <i class="bi bi-pencil-square text-sm"></i>
                                    </button>
                                `
                                :''
                        }


                        ${
                            canDelete
                                ?`
                                    <button
                                        type="button"
                                        onclick="deleteDocument(${item.id})"
                                        title="Delete"
                                        class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100"
                                    >
                                        <i class="bi bi-trash text-sm"></i>
                                    </button>
                                `
                                :''
                        }

                    </div>

                </div>

            </article>
        `).join('');
}


/*
|--------------------------------------------------------------------------
| File Preview
|--------------------------------------------------------------------------
*/

function filePreview(item){
    const extension=
        String(
            item.extension??''
        ).toLowerCase();

    if(isImageFile(extension)){
        return `
            <div class="h-16 w-16 shrink-0 overflow-hidden rounded-md border border-slate-200 bg-slate-50">

                <img
                    src="/api/documents/${item.id}/preview"
                    alt="${AdminUI.escapeHtml(item.title??'Document')}"
                    class="h-full w-full object-cover"
                    loading="lazy"
                    onerror="handlePreviewError(this,'${AdminUI.escapeHtml(extension)}')"
                >

            </div>
        `;
    }

    return `
        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-md border border-slate-100 bg-indigo-50 text-2xl text-indigo-600">
            <i class="${fileIcon(extension)}"></i>
        </div>
    `;
}


/*
|--------------------------------------------------------------------------
| Preview Error
|--------------------------------------------------------------------------
*/

window.handlePreviewError=
function(image,extension){
    const wrapper=
        image.parentElement;

    if(!wrapper){
        return;
    }

    wrapper.innerHTML=`
        <div class="flex h-full w-full items-center justify-center bg-indigo-50 text-2xl text-indigo-600">
            <i class="${fileIcon(extension)}"></i>
        </div>
    `;
};


/*
|--------------------------------------------------------------------------
| File Helpers
|--------------------------------------------------------------------------
*/

function isImageFile(extension){
    return [
        'jpg',
        'jpeg',
        'png',
        'webp'
    ].includes(extension);
}


function fileIcon(extension){
    const icons={
        pdf:'bi bi-file-earmark-pdf',
        doc:'bi bi-file-earmark-word',
        docx:'bi bi-file-earmark-word',
        xls:'bi bi-file-earmark-excel',
        xlsx:'bi bi-file-earmark-excel',
        ppt:'bi bi-file-earmark-ppt',
        pptx:'bi bi-file-earmark-ppt',
        zip:'bi bi-file-earmark-zip',
        jpg:'bi bi-file-earmark-image',
        jpeg:'bi bi-file-earmark-image',
        png:'bi bi-file-earmark-image',
        webp:'bi bi-file-earmark-image',
        csv:'bi bi-filetype-csv',
        txt:'bi bi-file-earmark-text'
    };

    return icons[
        String(extension??'').toLowerCase()
    ]??'bi bi-file-earmark';
}


/*
|--------------------------------------------------------------------------
| Open Modal
|--------------------------------------------------------------------------
*/

window.openDocumentModal=
function(item=null){
    editingDocument=item;

    AdminUI.resetForm(
        elements.form
    );

    AdminUI.clearError(
        'formError'
    );

    elements.file.value='';
    elements.selectedFileName.innerText=
        'Choose a file';


    if(item){
        elements.modalTitle.innerText=
            'Edit Document';

        elements.modalDescription.innerText=
            'Update document information or replace the uploaded file.';

        elements.modalIcon.className=
            'bi bi-pencil-square';

        elements.saveButton.innerText=
            'Update Document';

        elements.title.value=
            item.title??'';

        elements.category.value=
            item.category??'';

        elements.description.value=
            item.description??'';

        elements.visibility.value=
            item.visibility??'members';

        elements.isActive.checked=
            Boolean(item.is_active);

        elements.file.required=false;

        elements.fileRequiredMark.classList.add(
            'hidden'
        );

        elements.fileHint.innerText=
            'Leave empty to keep the existing file.';

        elements.selectedFileName.innerText=
            item.original_name??
            'Existing file';

    }else{
        elements.modalTitle.innerText=
            'Upload Document';

        elements.modalDescription.innerText=
            'Upload a new association document.';

        elements.modalIcon.className=
            'bi bi-cloud-arrow-up';

        elements.saveButton.innerText=
            'Upload Document';

        elements.visibility.value=
            'members';

        elements.isActive.checked=true;

        elements.file.required=true;

        elements.fileRequiredMark.classList.remove(
            'hidden'
        );

        elements.fileHint.innerText=
            'PDF, Office, image, ZIP or text file. Maximum 20 MB.';
    }


    AdminUI.openModal(
        'documentModal'
    );
};


/*
|--------------------------------------------------------------------------
| Close Modal
|--------------------------------------------------------------------------
*/

window.closeDocumentModal=
function(){
    AdminUI.closeModal(
        'documentModal'
    );

    editingDocument=null;
};


/*
|--------------------------------------------------------------------------
| Edit
|--------------------------------------------------------------------------
*/

window.editDocument=
function(id){
    const item=
        documents.find(
            item=>
                Number(item.id)===
                Number(id)
        );

    if(!item){
        Toast.error(
            'Document not found.'
        );

        return;
    }

    openDocumentModal(item);
};


/*
|--------------------------------------------------------------------------
| Submit Form
|--------------------------------------------------------------------------
*/

elements.form.addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        AdminUI.clearError(
            'formError'
        );

        const title=
            elements.title.value.trim();


        if(!title){
            AdminUI.showError(
                'formError',
                'Document title is required.'
            );

            return;
        }


        if(
            !editingDocument&&
            !elements.file.files.length
        ){
            AdminUI.showError(
                'formError',
                'Please select a document file.'
            );

            return;
        }


        const formData=
            AdminUI.formData({
                title,
                category:
                    elements.category.value.trim(),
                description:
                    elements.description.value.trim(),
                visibility:
                    elements.visibility.value,
                is_active:
                    elements.isActive.checked,
                file:
                    elements.file.files[0]??null
            });


        AdminUI.setLoading(
            elements.saveButton,
            editingDocument
                ?'Updating...'
                :'Uploading...'
        );


        try{
            const wasEditing=
                Boolean(
                    editingDocument
                );

            const pageAfterSave=
                wasEditing
                    ?currentPage
                    :1;


            await api(
                wasEditing
                    ?`/api/documents/${editingDocument.id}`
                    :'/api/documents',
                {
                    method:'POST',
                    body:formData
                }
            );


            closeDocumentModal();


            Toast.success(
                wasEditing
                    ?'Document updated successfully.'
                    :'Document uploaded successfully.'
            );


            await Promise.all([
                loadDocuments(
                    pageAfterSave
                ),
                loadFilters()
            ]);

        }catch(error){
            AdminUI.showError(
                'formError',
                AdminUI.extractError(error)
            );

        }finally{
            AdminUI.resetLoading(
                elements.saveButton
            );
        }
    }
);


/*
|--------------------------------------------------------------------------
| Delete Document
|--------------------------------------------------------------------------
|
| Global confirmation modal is handled by AdminUI.deleteRequest().
| No local modal and no native confirm().
|
*/

window.deleteDocument=
function(id){
    const item=
        documents.find(
            document=>
                Number(document.id)===
                Number(id)
        );

    if(!item){
        Toast.error(
            'Document not found.'
        );

        return;
    }


    AdminUI.deleteRequest(
        `/api/documents/${id}`,
        {
            message:
                `Delete "${item.title}" permanently? This action cannot be undone.`,

            successMessage:
                'Document deleted successfully.',

            onSuccess:async()=>{
                if(
                    documents.length===1&&
                    currentPage>1
                ){
                    currentPage--;
                }

                await Promise.all([
                    loadDocuments(
                        currentPage
                    ),
                    loadFilters()
                ]);
            }
        }
    );
};


/*
|--------------------------------------------------------------------------
| Download
|--------------------------------------------------------------------------
|
| Kept local intentionally because this endpoint returns a binary/blob,
| whereas api() is intended for JSON responses.
|
*/

window.downloadDocument=
async function(id){
    try{
        const response=
            await fetch(
                `/api/documents/${id}/download`,
                {
                    credentials:'same-origin',

                    headers:{
                        Accept:'application/octet-stream'
                    }
                }
            );


        if(response.status===401){
            window.location.href='/login';
            return;
        }


        if(!response.ok){
            let message=
                'Download failed.';

            try{
                const data=
                    await response.json();

                message=
                    data.message??
                    message;

            }catch{}

            Toast.error(message);

            return;
        }


        const blob=
            await response.blob();

        const disposition=
            response.headers.get(
                'Content-Disposition'
            )??'';

        let filename=
            'document';


        const utfMatch=
            disposition.match(
                /filename\*=UTF-8''([^;]+)/i
            );

        const normalMatch=
            disposition.match(
                /filename="?([^"]+)"?/i
            );


        if(utfMatch){
            filename=
                decodeURIComponent(
                    utfMatch[1]
                );

        }else if(normalMatch){
            filename=
                normalMatch[1];
        }


        const url=
            URL.createObjectURL(
                blob
            );

        const anchor=
            document.createElement(
                'a'
            );

        anchor.href=url;
        anchor.download=filename;

        document.body.appendChild(
            anchor
        );

        anchor.click();
        anchor.remove();

        URL.revokeObjectURL(
            url
        );

    }catch{
        Toast.error(
            'Unable to download the document.'
        );
    }
};


/*
|--------------------------------------------------------------------------
| Clear Filters
|--------------------------------------------------------------------------
*/

window.clearFilters=
function(){
    elements.search.value='';
    elements.categoryFilter.value='';
    elements.extensionFilter.value='';

    loadDocuments(1);
};


/*
|--------------------------------------------------------------------------
| Selected File
|--------------------------------------------------------------------------
*/

elements.file.addEventListener(
    'change',
    ()=>{
        const file=
            elements.file.files[0];

        elements.selectedFileName.innerText=
            file
                ?`${file.name} • ${AdminUI.formatBytes(file.size)}`
                :'Choose a file';
    }
);


/*
|--------------------------------------------------------------------------
| Initialize
|--------------------------------------------------------------------------
*/

async function initDocumentsPage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(
            initDocumentsPage,
            50
        );

        return;
    }


    elements.search.addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadDocuments(1)
        )
    );


    elements.categoryFilter.addEventListener(
        'change',
        ()=>loadDocuments(1)
    );


    elements.extensionFilter.addEventListener(
        'change',
        ()=>loadDocuments(1)
    );


    documentLoadingState();


    await Promise.all([
        loadDocuments(),
        loadFilters()
    ]);
}


if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initDocumentsPage
    );
}else{
    initDocumentsPage();
}
</script>

@endpush