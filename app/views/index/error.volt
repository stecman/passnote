<div class="fill-frame">
	<div class="err-message">
		<h1><span>{{ status }}</span> {{ message }}</h1>

		{% if exception is defined and constant('DEV_MODE') %}
			<pre>{{ exception }}</pre>
		{% endif %}
	</div>
</div>