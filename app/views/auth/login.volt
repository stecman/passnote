<div class="fill-frame">
	<div class="lgn">

	<img src="/img/mobius.svg" alt="△" class="lgn-symbol">

	<form method="post" autocomplete="off">

		{% include 'partials/form-messages.volt' %}

		{{ content() }}

		<input type="hidden" value="{{ security.getToken() }}" name="{{ security.getTokenKey() }}">

		<input type="email" name="user" placeholder="Email" autofocus autocomplete="off" required>
	
		<input type="password" name="password" placeholder="Password" required>

		<input type="number" name="token" placeholder="Token" autocomplete="off" pattern="[0-9]*" required>

		<input type="submit" value="Log in">

	</form>

	</div>
</div>
