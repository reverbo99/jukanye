@extends('layouts.admin')
@section('title', 'Orders')
@section('heading', 'Orders / Payments')
@section('content')
<div class="admin-card">
    <div style="display:flex;gap:.5rem;margin-bottom:1rem;">
        <a class="btn {{ $type==='' ? 'btn-accent' : 'btn-ghost' }}" href="{{ route('admin.orders.index') }}">All</a>
        <a class="btn {{ $type==='ticket' ? 'btn-accent' : 'btn-ghost' }}" href="{{ route('admin.orders.index', ['type'=>'ticket']) }}">Tickets</a>
        <a class="btn {{ $type==='donation' ? 'btn-accent' : 'btn-ghost' }}" href="{{ route('admin.orders.index', ['type'=>'donation']) }}">Donations</a>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Reference</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Paid</th>
            </tr>
        </thead>
        <tbody>
        @forelse($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->type }}@if($order->ticketTier) · {{ $order->ticketTier->name_en }}@endif</td>
                <td><code>{{ $order->reference }}</code></td>
                <td>
                    {{ $order->customer_name }}<br>
                    <span class="muted">{{ $order->customer_email }} {{ $order->customer_phone }}</span>
                </td>
                <td>{{ number_format($order->amount) }} {{ $order->currency }}</td>
                <td>{{ $order->status }}</td>
                <td>{{ optional($order->paid_at)->format('Y-m-d H:i') }}</td>
            </tr>
        @empty
            <tr><td colspan="7">No orders yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:1rem;">{{ $orders->links() }}</div>
</div>
@endsection
