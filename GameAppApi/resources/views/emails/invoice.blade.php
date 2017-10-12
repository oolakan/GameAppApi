<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Parcel-it | Invoice</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
</head>
<body class="skin-blue">
<div class="wrapper row-offcanvas">
    <aside class="right-side">
        <section class="content invoice">
            <!-- title row -->
            <div class="row">
                <div class="col-xs-12">
                    <h2 class="page-header" style="color: #0000ff">
                        <i class="fa fa-globe"></i> Parcel-it Receipt:
                        <small class="pull-right">{{date('Y:m:d')}}</small>
                    </h2>
                </div><!-- /.col -->
            </div>
            <!-- info row -->
            <div class="row invoice-info">
                <div class="col-sm-4 invoice-col">

                    <table width="80%">
                        <tr><td>  From
                                <address>
                                    <strong>Parcel-it.</strong><br>
                                    Treasure Gardens Estate,<br>
                                    Lekki, Lagos<br>
                                    Phone: 09090068616<br>
                                    Email: info@parcel-it.com<br>
                                </address></td>
                            <td align="right">
                                To
                                <address>
                                    <strong>{{$sender_name}}</strong><br>
                                    {{$sender_email}}<br>
                                    {{$sender_phone_no}}<br>
                                </address>
                            </td>
                        </tr>
                    </table>
                    <hr width="80%" align="left"/>
                </div><!-- /.col -->

                <div class="col-sm-4 invoice-col">
                    <b>Invoice #{{$invoice_no}}</b><br/>
                    <br/>
                    <b>Order ID:</b> {{$track_id}}<br/>
                    <b>Delivery Status:</b> {{$parcel_delivery_status}}<br/>
                </div><!-- /.col -->
            </div><!-- /.row -->

            <!-- Table row -->

            <div class="row">
                <div class="col-xs-12 table-responsive">
                    <hr width="80%" align="left"/>
                    <table class="table table-striped" width="80%">
                        <thead>
                        <tr>
                            <th align="left">Service</th>
                            <th align="right">Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td width = "70%">  <address>
                                    <strong>Parcel delivery service</strong><br>
                                    Receiver Name: {{$sender_name}}<br>
                                    Receiver Phone No: {{$sender_phone_no}}<br>
                                    Pickup Address: {{$parcel_pickup_address}}<br>
                                    Delivery Address: {{$parcel_destination_address}}<br>
                                </address>
                            </td >
                            <td align="right" width="30%">N{{number_format($total_delivery_cost,2)}}</td>
                        </tr>

                        </tbody>
                    </table>
                    <hr width="80%" align="left"/>
                </div><!-- /.col -->
            </div><!-- /.row -->

            <div class="row">
                <!-- accepted payments column -->
                <div class="col-xs-6">
                    <p class="lead">Payment Method: Card</p>
                    <hr width="80%" align="left"/>
                </div><!-- /.col -->
                <div class="col-xs-6">
                    <p class="lead">Amount Due {{date('Y:m:d')}}</p>
                        <table class="table" width="80%">
                            <tr>
                                <td align="left">Base Fare:</td>
                                <td align="right">N500.00</td>
                            </tr>
                            <tr>
                                <td align="left">Distance</td>
                                <td align="right">N{{number_format($delivery_distance_cost,2)}}</td>
                            </tr>

                            <tr>
                                <td align="left">Total:</td>
                                <td align="right">N{{number_format($total_delivery_cost,2)}}</td>
                            </tr>
                        </table>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </section><!-- /.content -->
    </aside><!-- /.right-side -->
</div><!-- ./wrapper -->
<!-- Bootstrap -->
</body>
</html>