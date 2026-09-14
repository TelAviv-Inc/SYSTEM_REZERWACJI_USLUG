<!-- resources/views/components/footer.blade.php -->
<footer {{ $attributes->merge(['class' => 'bg-white border-t border-brand-border py-4']) }}>
    <div class="max-w-7xl mx-auto text-center text-xs text-brand-muted">
        &copy; {{ date('Y') }} System Rezerwacji Usług
    </div>
</footer>