@php
    $canReview = (($currentStatusName ?? null) === 'Completed');
@endphp

@if(! $canReview)
    <div class="alert alert-secondary mb-0">
        Reviews are available after the order is completed.
    </div>
@else
    <div class="space-y-3">
        @foreach($orderItems as $item)
            @php
                $existingReview = isset($reviewsByServiceId) ? ($reviewsByServiceId->get($item->service_id) ?? null) : null;
            @endphp

            <div class="border rounded p-3 js-order-review-block">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="fw-semibold">{{ $item->service_name ?? 'Service' }}</div>
                    @if($existingReview)
                        <span class="badge bg-success">Reviewed</span>
                    @endif
                </div>

                <form action="{{ route('customer.orders.reviews.store', $order->purchase_order_id) }}" method="POST" class="mt-2" enctype="multipart/form-data" data-up-global-loader>
                    @csrf
                    <input type="hidden" name="service_id" value="{{ $item->service_id }}">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Rating</label>
                            @php
                                $selectedRating = (int) old('rating', $existingReview->rating ?? 0);
                                $rid = 'rating_' . ($item->service_id ?? 'x');
                            @endphp
                            <div class="btn-group" role="group" aria-label="Rating">
                                @for($r = 1; $r <= 5; $r++)
                                    <input type="radio" class="btn-check" name="rating" id="{{ $rid }}_{{ $r }}" value="{{ $r }}" autocomplete="off" @if($selectedRating === $r) checked @endif required>
                                    <label class="btn btn-sm btn-outline-warning" for="{{ $rid }}_{{ $r }}">
                                        <i class="bi bi-star-fill"></i>
                                    </label>
                                @endfor
                            </div>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label small fw-semibold">Comment (optional)</label>
                            <input type="text" name="comment" class="form-control form-control-sm" maxlength="2000"
                                   value="{{ old('comment', $existingReview->comment ?? '') }}"
                                   placeholder="Share your experience (quality, speed, communication, etc.)">
                        </div>
                    </div>

                    <div class="mt-2">
                        <label class="form-label small fw-semibold">Files (optional)</label>
                        <input type="file" name="review_files[]" class="form-control form-control-sm" multiple accept="image/*,application/pdf">
                        <div class="form-text">You can upload images or a PDF (max 50MB each).</div>

                        @if($existingReview && !empty($reviewFilesByReviewId) && !empty($reviewFilesByReviewId[$existingReview->review_id] ?? null))
                            <div class="mt-2">
                                <div class="small fw-semibold">Uploaded files</div>
                                <ul class="small mb-0">
                                    @foreach(($reviewFilesByReviewId[$existingReview->review_id] ?? []) as $f)
                                        <li>
                                            <a href="{{ asset('storage/' . ($f->file_path ?? '')) }}" target="_blank" rel="noopener">{{ $f->file_name ?? 'File' }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                    <div class="mt-2">
                        <button type="submit" class="btn btn-sm btn-primary" data-up-button-loader>
                            @if($existingReview)
                                Update Review
                            @else
                                Submit Review
                            @endif
                        </button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
@endif
