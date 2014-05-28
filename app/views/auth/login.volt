<div class="fill-frame">
	<div class="lgn">

	<img src="/img/mobius.svg" alt="△" class="lgn-symbol">

	<form method="post" autocomplete="off">

		{% include 'partials/form-messages.volt' %}

		{{ content() }}

		<input type="hidden" value="{{ security.getToken() }}" name="{{ security.getTokenKey() }}">

		{{ form.render('user', ['placeholder':'Email', 'autofocus': true, 'autocomplete': 'off']) }}

		{{ form.render('password', ['placeholder':'Password']) }}

		{{ form.render('token', ['placeholder':'Token', 'autocomplete': 'off']) }}

		<input type="submit" value="Log in">

	</form>

	</div>
</div>