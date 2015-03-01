<form method="post">

	{{ content() }}

	<input type="hidden" value="{{ security.getToken() }}" name="{{ security.getTokenKey() }}">

	<h3>Confirm deletion: <em>{{ object.title | e}}</em></h3>
	<hr/>

	<div class="form-group">
		<p>This will delete the current version and all previous versions of this object.</p>
	</div>

	<input type="submit" class="button-danger" value="Yes, really delete this">

</form>
