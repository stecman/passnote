<form method="post">

	{% include 'partials/form-messages.volt' %}

	{{ content() }}

	<input type="hidden" value="{{ security.getToken() }}" name="{{ security.getTokenKey() }}">

	<div class="form-group">
		<label for="title">Title</label>
		{{ form.render('title') }}
	</div>

	<div class="form-group">
		<label for="body">Content</label>
		{{ form.render('body', ['class': 'monospaced', 'rows': 20]) }}
	</div>

	<div class="form-group">
		<label for="description">Description</label>
		{{ form.render('description') }}
	</div>

	<div class="form-group">
		<label for="format">Format</label>
		{{ form.render('format') }}
	</div>

	<div class="form-group">
		<label for="key_id">Encrpytion Key</label>
		{{ form.render('key_id') }}
	</div>

	<input type="submit" value="Save">

</form>
