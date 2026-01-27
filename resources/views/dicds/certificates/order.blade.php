@extends('layouts.dicds')
@section('title', 'Order Certificates')
@section('content')
<div class="login-container">
    <div class="header-banner">
        <div class="florida-seal">Florida Department of<br><strong>HIGHWAY SAFETY & MOTOR VEHICLES</strong></div>
        <div class="tagline">"Making Highways Safe"</div>
    </div>

    <h1>Order Certificates</h1>

    <form method="POST" action="{{ route('dicds.certificates.store-order') }}">
        @csrf
        <table>
            <thead>
                <tr>
                    <th>Course Type</th>
                    <th>Certificate Count</th>
                    <th>Price per Certificate</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>BDI - Internet</td>
                    <td><input type="number" name="courses[bdi_internet][count]" min="0" value="0" class="form-control"></td>
                    <td>$2.00</td>
                    <td class="total">$0.00</td>
                </tr>
                <tr>
                    <td>ADI - Internet</td>
                    <td><input type="number" name="courses[adi_internet][count]" min="0" value="0" class="form-control"></td>
                    <td>$2.00</td>
                    <td class="total">$0.00</td>
                </tr>
                <tr>
                    <td>TLSAE - Internet</td>
                    <td><input type="number" name="courses[tlsae_internet][count]" min="0" value="0" class="form-control"></td>
                    <td>$2.00</td>
                    <td class="total">$0.00</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">Grand Total</th>
                    <th id="grandTotal">$0.00</th>
                </tr>
            </tfoot>
        </table>

        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" class="btn">Submit Order</button>
            <a href="{{ route('dicds.provider-menu') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
