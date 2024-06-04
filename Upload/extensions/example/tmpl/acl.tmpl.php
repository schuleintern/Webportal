<div class="box">
	<div class="box-body">
        <h3>Your ACL:</h3>
        <p>
            <?php
            echo '<pre>';
            print_r(json_decode($acl));
            echo '</pre>';
            ?>
        </p>
        <h3>Module ACL:</h3>
        <p>
            <?php
            echo '<pre>';
            print_r(json_decode($aclAll));
            echo '</pre>';
            ?>
        </p>
	</div>
</div>