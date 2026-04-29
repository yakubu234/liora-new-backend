<div class="form-group">
    <label>Guest Name</label>
    <input class="form-control" type="text" name="name" value="{{ old('name', $testimonial->name ?? '') }}" required>
</div>
<div class="form-group">
    <label>Guest Role</label>
    <input class="form-control" type="text" name="role" value="{{ old('role', $testimonial->role ?? '') }}" required>
</div>
<div class="form-group">
    <label>Rating</label>
    <select class="form-control" name="rating" required>
        @for ($rating = 5; $rating >= 1; $rating--)
            <option value="{{ $rating }}" {{ (int) old('rating', $testimonial->rating ?? 5) === $rating ? 'selected' : '' }}>
                {{ $rating }} Star{{ $rating === 1 ? '' : 's' }}
            </option>
        @endfor
    </select>
</div>
<div class="form-group">
    <label>Status</label>
    <select class="form-control" name="status" required>
        <option value="active" {{ old('status', $testimonial->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
        <option value="inactive" {{ old('status', $testimonial->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
    </select>
</div>
<div class="form-group mb-0">
    <label>Testimonial Quote</label>
    <textarea class="form-control" name="quote" rows="5" required>{{ old('quote', $testimonial->quote ?? '') }}</textarea>
</div>
