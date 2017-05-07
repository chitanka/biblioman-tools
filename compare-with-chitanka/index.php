<!DOCTYPE html>
<html lang="bg">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Сравняване на книги между Библиоман и Моята библиотека</title>

	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>

<style>
	h2.number {
		font-size: 600%;
		text-decoration: underline double silver;
	}
	h3 {
		font-size: 130%;
	}
	hr {
		border: 0;
		height: 8px;
		background: #ddd;
		background-image: linear-gradient(to right, white, #ccc, white);
		margin: 3em 1em;
	}
	.same {
		background-color: lightgreen;
	}
	.different {
		background-color: lightsalmon;
	}
</style>
<body>
<div class="container">

	<div class="page-header">
		<h1>Сравняване на книги между Библиоман и Моята библиотека</h1>
	</div>

	<?php foreach (file(__DIR__.'/data.csv') as $i => $row): ?>
		<?php
		list($bibliomanId, $chitankaId, $bibliomanTitle, $chitankaTitle, $bibliomanAuthor, $chitankaAuthor) = explode("\t", $row);
		?>
	<div class="row record" data-ids="<?= $bibliomanId ?>-<?= $chitankaId ?>">
		<div class="col-md-2">
			<h2 class="number"><?= ($i+1) ?></h2>
		</div>
		<div class="col-md-4">
			<h3><?= $bibliomanTitle, ' – ', $bibliomanAuthor ?></h3>
			<a class="thumbnail" href="//biblioman.chitanka.info/books/<?= $bibliomanId ?>" target="_blank">
				<img src="//biblioman.chitanka.info/books/<?= $bibliomanId ?>.cover?size=150">
			</a>
		</div>
		<div class="col-md-4">
			<h3><?= $chitankaTitle, ' – ', $chitankaAuthor ?></h3>
			<a class="thumbnail" href="//chitanka.info/book/<?= $chitankaId ?>" target="_blank">
				<img src="//chitanka.info/book/<?= $chitankaId ?>.cover?size=150">
			</a>
		</div>
		<div class="col-md-2">
			<a href="#" class="btn btn-success mark" data-class="same" style="display: block; margin-top: 5em">Еднакви са</a>
			<a href="#" class="btn btn-danger mark" data-class="different" style="display: block; margin-top: 3em">Различни са</a>
		</div>
	</div>
	<hr>
	<?php endforeach ?>

	<div class="text-center">
		<a href="#" class="btn btn-info showResults">Показване на резултатите</a>
	</div>

</div> <!-- /container -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<script>
function classFormRecord($record) {
	return 'class-' + $record.data('ids');
}
$(function() {
	$(document).on("click", "a.mark", function() {
		var $record = $(this).closest(".record");
		var markedClass = $(this).data('class');
		localStorage.setItem(classFormRecord($record), markedClass);
		$record.removeClass('same different').addClass(markedClass);
		return false;
	});
	$('.record').each(function() {
		var $record = $(this);
		var markedClass = localStorage.getItem(classFormRecord($record));
		if (markedClass) {
			$record.addClass(markedClass);
		}
	});
	$('.showResults').on('click', function () {
		var results = $('.record.same').map(function () {
			return $(this).data('ids');
		}).get().join("\n");
		$('<textarea class="form-control" rows="10"></textarea>').html(results).insertBefore(this);
		return false;
	});
});
</script>
</body>
</html>
