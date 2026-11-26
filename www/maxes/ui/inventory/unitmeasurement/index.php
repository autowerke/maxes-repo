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
        <li id="detailrec" class="maxes02_2"><a href="#maxes02_1_2">Unit of Measurement</a></li>
    </ul>
    <div class="maxes04 shadow">
        <div id="maxes02_1_1" class="tab maxes02_1">
            <div class="page-header-list">
			<div class="tablelist">
				<table id="mxtable_measureunit" class="display">
				  <thead>
					<tr>
					  <th>Unit of Measurement</th>				
					  <th>Description</th>
					</tr>
				  </thead>
				  
				  <tfoot>
					<tr>
					  <th>Unit of Measurement</th>				
					  <th>Description</th>
					</tr>
				  </tfoot>
		
				  <tbody>
					<tr>
					  <td data-label="Measure Unit">Unit of Measure</td>
					  <td data-label="Description">Description</td>
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
								<td>Unit of Measure
								</td>
								<td><input type="text" name = "frommeasureunit" data-attribute="frommeasureunit" data-input="required">
								</td>
							</tr>
							<tr>
								<td>Description
									</td>
									<td><input type="text" name = "description" data-attribute="description">
									</td>
							</tr>
							<tr>
								<td>Abbreviation
								</td>
								<td><input type="text" name = "abbreviation" data-attribute="abbreviation">
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
