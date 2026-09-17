@extends('layouts.backoffice')

@section('title', 'Packing List Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-0">Packing Checklist Management</h3>
            <p class="text-muted small mb-0">Season: <strong>{{ $season ? $season->name : 'None' }}</strong></p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            @php
                $isReleased = false;
                if ($items->count() > 0) {
                    $firstGroup = $items->first();
                    $isReleased = $firstGroup->first()?->is_released ?? false;
                }
            @endphp

            <!-- Print / Preview PDF -->
            <a href="{{ route('backoffice.packing.pdf') }}" target="_blank" class="btn btn-outline-light btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                <span>Print / Preview PDF</span>
            </a>

            <!-- Broadcast Notification to all Campers & Parents -->
            @if($isReleased)
                <form action="{{ route('backoffice.packing.broadcast') }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Send live packing list notification and emails to all registered parents and teens?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning btn-sm d-flex align-items-center gap-1" title="Re-send notification broadcast to all parents and teens">
                        <i class="bi bi-megaphone-fill text-warning"></i>
                        <span>Broadcast Notification</span>
                    </button>
                </form>
            @endif

            <!-- Release / Unrelease Toggle -->
            <form action="{{ route('backoffice.packing.toggle-release') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn {{ $isReleased ? 'btn-outline-danger' : 'btn-success' }} btn-sm d-flex align-items-center gap-1">
                    <i class="bi {{ $isReleased ? 'bi-lock-fill' : 'bi-unlock-fill' }}"></i>
                    <span>{{ $isReleased ? 'Unrelease (Hide)' : 'Release Checklist to Campers' }}</span>
                </button>
            </form>

            <!-- Add Item Trigger -->
            <button type="button" class="btn btn-camp-red btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addItemModal">
                <i class="bi bi-plus-lg"></i>
                <span>Add Item</span>
            </button>
        </div>
    </div>

    <!-- Release Status Banner -->
    <div class="alert {{ $isReleased ? 'alert-success' : 'alert-secondary' }} d-flex align-items-center justify-content-between p-3 mb-4 shadow-sm">
        <div class="d-flex align-items-center gap-2">
            <i class="bi {{ $isReleased ? 'bi-check-circle-fill text-success' : 'bi-lock-fill text-muted' }} fs-4"></i>
            <div>
                <strong>Visibility Status:</strong> 
                {{ $isReleased ? 'Live & Released — Campers & Parents can view and download PDF.' : 'Private Draft — Hidden from Teen and Parent Dashboards.' }}
            </div>
        </div>
        <span class="badge bg-{{ $isReleased ? 'success' : 'secondary' }} text-uppercase">
            {{ $isReleased ? 'Released' : 'Hidden' }}
        </span>
    </div>

    <!-- Items Grouped by Category -->
    <div class="row g-4">
        @forelse($items as $category => $catItems)
            <div class="col-md-6 col-lg-4">
                <div class="camp-card p-4 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center border-bottom border-secondary border-opacity-25 pb-2 mb-3">
                        <h5 class="fw-bold text-danger mb-0">
                            <i class="bi bi-tag-fill me-1"></i> {{ $category }}
                        </h5>
                        <span class="badge bg-dark border border-secondary text-white-50 small">{{ $catItems->count() }} items</span>
                    </div>

                    <ul class="list-group list-group-flush small flex-grow-1">
                        @foreach($catItems as $item)
                            <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-start border-secondary border-opacity-25">
                                <div class="pe-2">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="fw-bold text-white">{{ $item->item_name }}</span>
                                        @if($item->is_essential)
                                            <span class="badge bg-danger" style="font-size: 9px;">Essential</span>
                                        @endif
                                    </div>
                                    @if($item->notes)
                                        <div class="text-muted" style="font-size: 11px;">{{ $item->notes }}</div>
                                    @endif
                                </div>

                                <!-- Action Buttons: Edit & Remove -->
                                <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                    <button type="button" class="btn btn-sm btn-outline-secondary p-1 lh-1" data-bs-toggle="modal" data-bs-target="#editItemModal{{ $item->id }}" title="Edit this item">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger p-1 lh-1" data-bs-toggle="modal" data-bs-target="#deleteItemModal{{ $item->id }}" title="Remove this item">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="p-5 text-center text-muted camp-card">
                    <i class="bi bi-backpack fs-1 mb-2 d-block text-danger"></i>
                    <h5>No Items Added Yet</h5>
                    <p class="small mb-3">Add clothing, bedding, and supplies for campers to bring to camp.</p>
                    <button type="button" class="btn btn-camp-red btn-sm" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        <i class="bi bi-plus-lg me-1"></i> Add First Item
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Add Item Modal -->
    <div class="modal fade" id="addItemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background-color: var(--camp-surface); border-color: var(--camp-border);">
                <form action="{{ route('backoffice.packing.store') }}" method="POST">
                    @csrf
                    <div class="modal-header border-secondary border-opacity-25">
                        <h5 class="modal-title fw-bold text-white"><i class="bi bi-plus-circle text-danger me-2"></i>Add Packing Item</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-white">Category</label>
                            <input type="text" class="form-control" name="category" list="categories" required placeholder="e.g. Clothing & Footwear">
                            <datalist id="categories">
                                <option value="Clothing & Footwear">
                                <option value="Bedding & Sleep">
                                <option value="Toiletries & Personal Hygiene">
                                <option value="Bible & Stationery">
                                <option value="Special Activities & Sports">
                                <option value="Prohibited Items">
                            </datalist>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-white">Item Name</label>
                            <input type="text" class="form-control" name="item_name" required placeholder="e.g. Sleeping Bag / Warm Blanket">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-white">Notes / Guidelines</label>
                            <input type="text" class="form-control" name="notes" placeholder="e.g. Must be labeled with camper name">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_essential" value="1" id="is_essential" checked>
                            <label class="form-check-label fw-semibold text-white" for="is_essential">
                                Mark as Essential / Mandatory
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary border-opacity-25">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-camp-red btn-sm">Add Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit & Delete Modals for Each Item -->
    @foreach($items as $cat => $catItems)
        @foreach($catItems as $item)
            <!-- Edit Item Modal -->
            <div class="modal fade" id="editItemModal{{ $item->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content" style="background-color: var(--camp-surface); border-color: var(--camp-border);">
                        <form action="{{ route('backoffice.packing.update', $item->id) }}" method="POST">
                            @csrf
                            <div class="modal-header border-secondary border-opacity-25">
                                <h5 class="modal-title fw-bold text-white"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Packing Item</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-white">Category</label>
                                    <input type="text" class="form-control" name="category" value="{{ $item->category }}" list="categories" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-white">Item Name</label>
                                    <input type="text" class="form-control" name="item_name" value="{{ $item->item_name }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-white">Notes / Guidelines</label>
                                    <input type="text" class="form-control" name="notes" value="{{ $item->notes }}">
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_essential" value="1" id="edit_is_essential_{{ $item->id }}" {{ $item->is_essential ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-white" for="edit_is_essential_{{ $item->id }}">
                                        Mark as Essential / Mandatory
                                    </label>
                                </div>
                            </div>
                            <div class="modal-footer border-secondary border-opacity-25">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-warning btn-sm text-dark fw-bold">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Item Confirmation Modal -->
            <div class="modal fade" id="deleteItemModal{{ $item->id }}" tabindex="-1">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content" style="background-color: var(--camp-surface); border-color: var(--camp-border);">
                        <form action="{{ route('backoffice.packing.destroy', $item->id) }}" method="POST">
                            @csrf
                            <div class="modal-header border-secondary border-opacity-25">
                                <h6 class="modal-title fw-bold text-danger"><i class="bi bi-trash me-1"></i>Delete Item</h6>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-white">
                                <p class="small mb-0">Are you sure you want to remove <strong>{{ $item->item_name }}</strong> from the packing checklist?</p>
                            </div>
                            <div class="modal-footer border-secondary border-opacity-25">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger btn-sm">Remove Item</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach

</div>
@endsection
