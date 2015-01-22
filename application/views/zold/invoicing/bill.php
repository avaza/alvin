   <!-- Begin of #main -->
    <div id="main" role="main">
    
    <!-- Begin of titlebar/breadcrumbs -->
		<div id="title-bar">
			<ul id="breadcrumbs">
				<li><a href="dashboard" title="Home"><span id="bc-home"></span></a></li>
				<li class="no-hover"><a>Invoicing</a></li>
				<li class="no-hover">Client Invoicing</li>
			</ul>
		</div> <!--! end of #title-bar -->

	<div class="shadow-bottom shadow-titlebar"></div>


<!-- Begin of #main-content -->
<div id="main-content">
  	<div class="container_12">
		<div class="grid_12">
			<h1>Client Invoicing</h1>
			<p>Select a date range and Client ID below to export call details.</p>
		</div>

		<div class="grid_6">
			<div class="block-border">
				<div class="block-header">
					<h1>Invoicing Selections</h1><span></span>
				</div>
				<?php echo form_open('billform', 'id="billform" class="block-content form" action=""');?>
				<div class="block-content">					
					<fieldset>
						<legend>Select Date Range</legend>
						<div class="_50">
							<p>
								<label for="datefrom">Starting Date</label>
								<input id="datefrom" name="datefrom" class="required" type="text" value="" />
							</p>
						</div>
						<div class="_50">
							<p>
								<label for="dateto">Ending Date</label>
								<input id="dateto" name="dateto" class="required" type="text" value="" />
							</p>
						</div>
					</fieldset>						
					<fieldset>
						<legend>Select Client ID</legend>
						<div class="_50">
							<p><label for="client_id">Client ID</label>
							<select id="client_id" name="client_id" class="_50"></select>
							</p>
						</div>
					</fieldset>
					<div id="errors">
					<?php echo validation_errors('<div class="alert error"><span class="hide">x</span><strong>', '</strong></div>');?>
					<?php if(isset($msg)){echo '<div>' . $msg . '</div>';}?>
					</div>
				</div>
				<div class="clear"></div>
	                <div class="block-actions">
	                    <ul class="actions-right">
	                        <li><a class="button" id="data_get" href="javascript:void(0);">Export Small Invoices</a></li>
	                        <li><a class="button" id="invoice_get" href="javascript:void(0);">Export Selected Invoice</a></li>
	                    </ul>
	                </div>
	            </form>
			</div>
		</div>
		<div class="grid_6" id="complet" >
			<div class="block-border">
				<div class="block-header">
					<h1>Completed Invoices</h1><span></span>
				</div>
				<div class="block-content">	
					<div id="invoiced">
						<h3>Select a Date Range ...</h3><!-- Already invoiced Clients Here-->
					</div>
				</div>
				<div class="clear"></div>
	            </form>
			</div>
		</div>
	</div>
<div>
<script type="text/javascript" src="<?php echo base_url();?>assets/javascripts/application/invoicing.js"></script>
