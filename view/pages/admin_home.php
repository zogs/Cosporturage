<div class="page-header">
	<h1>Welcome</h1>		
</div>

<div>
	<a href="https://www.google.com/analytics/web/?hl=fr&pli=1#report/visitors-overview/a42468932w72230201p74541839/" target="_blank">Google Analytics</a>
</div>

<div>
	<h4>Utilisateurs</h4>
	<p>
		Total : <strong><?php echo $totalUsers;?></strong>
	</p>
	<p>
		Inscrit aujourd'hui :<strong><?php echo $todayRegistering;?></strong>
	</p>
	<p>
		Inscription / mois :
		<?php foreach ($monthRegistering as $year => $months):?>
			<table class="table-bordered table-hover table-stripped" style="width:100%;">
				<th></th>
				<?php foreach($months as $month => $number): ?>
					<th><?php echo $month;?></th>
				<?php endforeach; ?>
				<tr>
					<td><?php echo $year;?></td>
					<?php 
					reset($months);
					foreach($months as $month => $number): ?>
					<td style="text-align:center"><?php echo $number;?></td>
					<?php endforeach; ?>
				</tr>
		<?php endforeach; ?>
		<table>			
	</p>
</div>

<div>
	<h4>Evenements</h4>
	<p>
		Total : <strong><?php echo $totalEvents;?></strong>
	</p>
	<p>
		Ont lieu aujourd'hui: <strong><?php echo $nbEventsToday;?></strong>
	</p>
	<p>
		Evts / mois :
		<?php foreach ($nbEventsPerMonth as $year => $months):?>
			<table class="table-bordered table-hover table-stripped" style="width:100%;">
				<th></th>
				<?php foreach($months as $month => $number): ?>
					<th><?php echo $month;?></th>
				<?php endforeach; ?>
				<tr>
					<td><?php echo $year;?></td>
					<?php 
					reset($months);
					foreach($months as $month => $number): ?>
					<td style="text-align:center"><?php echo $number;?></td>
					<?php endforeach; ?>
				</tr>
		<?php endforeach; ?>
		<table>			
	</p>
</div>