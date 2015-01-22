<body class="special-page">
<script type="text/javascript">
var page = <?php echo "'" . $page . "'"; ?>;
</script>
<script type="text/javascript" src="<?php echo base_url();?>assets/javascripts/application/call_tracking/calls_viewer.js"></script>
    <div style="padding:10px;">
        <table id="calls-table" class="table">
            <thead>
                <tr>
                    <td>Connected By</td>
                    <td>Job Number</td>
                    <td>Access Code</td>
                    <td>Answered</td>
                    <td>Client ID</td>
                    <td>Caller Number</td>
                    <td>Rep Name</td>
                    <td>Language</td>
                    <td>Int ID</td>
                    <td>Int Name</td>
                    <td>Start Time</td>
                    <td>End Time</td>
                    <td>Dropped?</td>
                    <td>CO #</td>
                    <td>IR</td>
                </tr>
            </thead>
            <tbody id="calls-table-body">                     
            </tbody>
        </table>
    </div>
</body>