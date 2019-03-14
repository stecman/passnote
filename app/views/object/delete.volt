<form method="post">

	{{ content() }}

	<input type="hidden" value="{{ security.getToken() }}" name="{{ security.getTokenKey() }}">


	{% if isVersion %}
		<h3>Confirm version deletion: <em>{{ object.master.title | e }}</em></h3>
	{% else %}
		<h3>Confirm deletion: <em>{{ object.title | e }}</em></h3>
	{% endif %}

	<hr/>

	<div class="form-group">
		{% if isVersion %}
			<p>This will delete the version at {{ object.getDateCreated('j M Y, g:ia') }}.</p>
		{% else %}
			<p>This will delete the current version and all previous versions of this object.</p>
		{% endif %}
	</div>

	<input type="submit" class="button-danger" value="Yes, really delete this">

</form>
