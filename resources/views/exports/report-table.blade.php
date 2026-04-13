<table>
    <tr>
        <td colspan="{{ max(count($headings), 1) }}"><strong>{{ strtoupper($title) }}</strong></td>
    </tr>

    @foreach ($meta as $label => $value)
        <tr>
            <td><strong>{{ $label }}</strong></td>
            <td colspan="{{ max(count($headings) - 1, 1) }}">{{ $value }}</td>
        </tr>
    @endforeach

    <tr></tr>

    <thead>
        <tr>
            @foreach ($headings as $heading)
                <th style="background-color: #facc15; font-weight: bold; border: 1px solid #d1d5db;">{{ $heading }}</th>
            @endforeach
        </tr>
    </thead>

    <tbody>
        @forelse ($rows as $row)
            <tr>
                @foreach ($row as $value)
                    <td style="border: 1px solid #e5e7eb;">{{ $value }}</td>
                @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="{{ max(count($headings), 1) }}">Tidak ada data</td>
            </tr>
        @endforelse

        @if ($footerRow)
            <tr>
                @foreach ($footerRow as $value)
                    <td style="background-color: #fed7aa; font-weight: bold; border: 1px solid #d1d5db;">{{ $value }}</td>
                @endforeach
            </tr>
        @endif
    </tbody>
</table>
