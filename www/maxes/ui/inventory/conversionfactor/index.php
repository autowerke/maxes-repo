<!DOCTYPE html>
<html>
<title>
</title>
<head>
	<?php
	include ("../../../applications/ui/ui.php");
	?>
	</head>
<body>
<div class="maxes-set"></div>
<div class="maxes-set2"></div>
<div class="maxes05">
<div class="maxes_viewport01">
<div class="maxest"></div>
	<nav class ="maxes05_1">
		
	</nav>
</div>	
<div class="maxes_viewport02">

	<nav class ="maxesph05_1">		
	</nav>
	<div class="maxest"></div>
	<div class="maxesph05_2">
	
	
	</div>
</div>
</div>
<div class="maxes06">
	<nav class="maxes06_1">
	</nav>
</div>
<div class="maxes00">

<div class="maxes01">
    <ul class="maxes02">
        <li class="maxes02_1"><a href="#maxes02_1_1">List</a></li>
        <li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Conversion Factor</a></li>
    </ul>
    <div class="maxes04 shadow">
        <div id="maxes02_1_1" class="tab maxes02_1">
            <div class="page-header-list">
			<div class="tablelist">
				<table id="mxtable_conversionfactor" class="display">
				  <thead>
					<tr>
					  <th>From Unit Measure</th>				
					  <th>To Unit Measure</th>
					  <th>Conv.Factor</th>
					  <th>Specified Item</th>
					</tr>
				  </thead>
				  
				  <tfoot>
					<tr>
					  <th>From Unit Measure</th>				
					  <th>To Unit Measure</th>
					  <th>Conv.Factor</th>
					  <th>Specified Item</th>
					</tr>
				  </tfoot>
		
				  <tbody>
					<tr>
					  <td data-label="From Unit Measure">From Unit Measure</td>
					  <td data-label="To Unit Measure">To Unit Measure</td>
					  <td data-label="Conv. Factor">Conv.Factor</td>
					   <td data-label="Specified Item">Item</td>
					</tr>
				  </tbody>
				</table>
			</div>
			</div>
        </div>
 
        <div id="maxes02_1_2" class="maxes03">
			<div class="page-header">
					<table class="tbldata">
							<tr>
								<td>From Unit of Measure
								</td>
								<td><input type="text" name = "frommeasureunit" data-attribute="frommeasureunit" data-input="required">
								</td>
							</tr>
							<tr>
								<td>To Unit of Measure
									</td>
									<td><input type="text" name = "tomeasureunit" data-attribute="tomeasureunit" data-input="required">
									</td>
							</tr>
							<tr>
								<td>Conversion Factor
								</td>
								<td><input type="text" name = "conversionfactor" data-attribute="conversionfactor" data-input="required">
								</td>
							</tr>
							<tr>
								<td>Specific Item Number
								</td>
								<td><input type="text" name = "itemnum" data-attribute="itemnum">
								</td>
							</tr>
							</table>
			</div>
			
        </div>
 
    </div>
</div>
</div>
</body>
</html>
