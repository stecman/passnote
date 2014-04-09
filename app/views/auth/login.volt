<div class="fill-frame">
	<div class="lgn">

	<img src="/img/mobius.svg" alt="△" class="lgn-symbol">

	<form method="post" autocomplete="off">

		{% if form.getMessages() | length %}
		<div class="alert alert-error">
		<p><strong>Whoops, there {% if form.getMessages() | length > 1 %}were some problems{% else %}was a problem{% endif %}:</strong></p>
		{% for message in form.getMessages() %}
			<p>{{ message }}</p>
		{% endfor %}
		</div>
		{% endif %}

		{{ content() }}

		<input type="hidden" value="{{ security.getToken() }}" name="{{ security.getTokenKey() }}">

		{{ form.render('user', ['placeholder':'Username', 'autofocus': true, 'autocomplete': 'off']) }}

		{{ form.render('password', ['placeholder':'Password']) }}

		{{ form.render('token', ['placeholder':'Token', 'autocomplete': 'off']) }}

		<input type="submit" value="Log in">

	</form>

	</div>
</div>