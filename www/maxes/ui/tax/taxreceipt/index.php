<!DOCTYPE html>
<html>
	<title></title>
	<head>
		<?php
			include ("../../../applications/ui/ui.php");
			?>
	</head>
	<body id="page-top" xboset="taxreceipt" data-appkeynum = "taxreceiptnum">
		<div id="wrapper">
			<div id='sidebarmenu'></div>
			<div id="content-wrapper" class="d-flex flex-column">
				<div id="content">
					<div class="eat-toolbars"></div>
					<div class="container-fluid">
						<div class="maxes01">
							<ul class="maxes02">
								<li id="list" class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
								<li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Tax Receipt</a></li>
							</ul>
							<div class="maxes04 shadow">
								<div id="maxes02_1_1" class="tab maxes02_1">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<div class="page-header-list">
												<div class="tablelist">
													<table id="mxtable_taxreceipt" class="display"></table>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div id="maxes02_1_2" class="maxes03">
									<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
										<div class='eat_apptitle'></div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<div class="page-header">
												<table class="tbldata">
													<tr>
														<td>Num. Efaktur
														</td>
														<td><input type="text" data-attribute="taxreceiptnum" data-input="required" data-key="value" data-type="upper">
														</td>
													</tr>
													<tr>
														<td>Description
														</td>
														<td><input type="text" data-attribute="description">
														</td>
													</tr>
												</table>
											</div>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl">Detail</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Status</td>
													<td><input type="text" data-attribute="status" data-input="readonly"> </td>
												</tr>
												<tr>
													<td>Status Date</td>
													<td><input type="text" data-attribute="statusdate" data-input="readonly" data-type="date"> </td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl3">Invoice </td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Invoice</td>
													<td><input type="text" data-attribute="invoicenum" data-input="readonly" data-childrentable="invoice" data-childrencolumn="invoicenum"> </td>
												</tr>
												<tr>
													<td>Sub Total </td>
													<td><input type="text" data-attribute="invoicenum.totalcost" data-input="readonly" data-type="decimal"> </td>
												</tr>
												<tr>
													<td>Quantity</td>
													<td><input type="text" data-attribute="quantity" data-input="readonly" data-type="decimal"> </td>
												</tr>
												<tr>
													<td><font color=blue><b>Total Tax</b></font></td>
													<td><input type="text" data-attribute="lineprice" data-input="readonly" data-type="decimal"> </td>
												</tr>
												<tr>
													<td>Total </td>
													<td><input type="text" data-attribute="invoicenum.totalcost" data-input="readonly" data-type="decimal"> </td>
												</tr>
												<tr>
													<td>Debit Account Code</td>
													<td><input type="text" data-attribute="gldebitacct"  data-lookup="chartofaccount"></td>
												</tr>
												<tr>
													<td>Account Code</td>
													<td><input type="text" data-attribute="glcreditacct" data-lookup="chartofaccount"></td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-12">
											<table width=100%>
												<tr>
													<td class="maxes_sct_tbl3">Sales Order</td>
												</tr>
											</table>
										</div>
									</div>
									<div class="row eatfix">
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>Sales Order</td>
													<td><input type="text" data-childfld="child" data-attribute="invoicenum.sonum"  data-input="readonly" data-childrentable="so" data-childrencolumn="sonum"></td>
												</tr>
												<tr>
													<td>Sales Order</td>
													<td><input type="text" data-childfld="child" data-attribute="invoicenum.sonum.description"  data-input="readonly" data-childrentable="so" data-childrencolumn="sonum"></td>
												</tr>
												<tr>
													<td>Discount (%)</td>
													<td><input type="text" data-childfld="child" data-attribute="invoicenum.sonum.discountpercentage"  data-input="readonly" data-childrentable="so" data-childrencolumn="sonum"></td>
												</tr>
												<tr>
													<td>Discount</td>
													<td><input type="text" data-childfld="child" data-attribute="invoicenum.sonum.discountedtotalprice"  data-input="readonly" data-type="decimal" data-childrentable="so" data-childrencolumn="sonum"></td>
												</tr>
												<tr>
													<td>Tax </td>
													<td><input type="text" data-childfld="child" data-attribute="invoicenum.sonum.discountedtotaltax"  data-input="readonly" data-type="decimal" data-childrentable="so" data-childrencolumn="sonum"></td>
												</tr>
												<tr>
													<td>Status</td>
													<td><input type="text" data-childfld="child" data-attribute="invoicenum.sonum.status"  data-input="readonly" data-childrentable="so" data-childrencolumn="sonum"></td>
												</tr>
											</table>
										</div>
										<div class="col-md-4">
											<table class="tbldata">
												<tr>
													<td>ID Billing</td>
													<td><input type="text" data-attribute="idbilling" > </td>
												</tr>
												<tr>
													<td>Active hingga</td>
													<td><input type="text" data-attribute="activedate" data-type="date"> </td>
												</tr>
												<tr>
													<td>Paid </td>
													<td><input type="text" data-attribute="invoicenum.sonum.paidprice" data-input="readonly" data-type="decimal" data-childrentable="so" data-childrencolumn="sonum"> </td>
												</tr>
												<tr>
													<td>Unpaid</td>
													<td><input type="text" data-attribute="invoicenum.sonum.unpaidprice" data-input="readonly" data-type="decimal" data-childrentable="so" data-childrencolumn="sonum"> </td>
												</tr>
												<tr>
													<td>Company</td>
													<td><input type="text" data-attribute="invoicenum.orgcode" data-input="readonly"> </td>
												</tr>
												<tr>
													<td>Site</td>
													<td><input type="text" data-attribute="invoicenum.sitecode" data-input="readonly"> </td>
												</tr>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>