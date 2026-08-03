@if (empty($ruta))
    <div class="dato-actual">
        No registrado
    </div>
@else
    @php
        $extension = strtolower(
            pathinfo($ruta, PATHINFO_EXTENSION)
        );

        $url = Storage::disk('public')->url($ruta);
    @endphp

    @if ($extension === 'pdf')
        <a
            href="{{ $url }}"
            target="_blank"
            class="btn btn-outline-primary btn-sm"
        >
            <i class="fas fa-file-pdf mr-1"></i>
            Ver documento PDF
        </a>
    @else
        <a href="{{ $url }}" target="_blank">
            <img
                src="{{ $url }}"
                class="documento-preview"
                alt="Documento"
            >
        </a>
    @endif
@endif
