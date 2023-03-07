<template>
	<k-section class="k-mrflix-protocol">
		<div class="k-mrflix-protocol-day" v-for="day in days">
			<k-headline class="k-mrflix-protocol-day-headline">{{ day.date }}</k-headline>
			<div class="k-mrflix-protocol-entry" v-for="row in day.rows">
				<div class="k-mrflix-protocol-entry-meta">
					<div class="k-mrflix-protocol-entry-meta-time">{{ row.time }}</div>
					<k-icon :type="row.icon" v-if="row.icon" />
				</div>
				<div class="k-mrflix-protocol-entry-text">
					<div class="k-mrflix-protocol-entry-message" v-html="row.message"></div>
					<div class="k-mrflix-protocol-entry-detail" v-if="row.detail">{{ row.detail }}</div>
				</div>
			</div>
		</div>
		<k-empty
			v-if="days.length === 0"
			class="k-mrflix-protocol-empty"
		>
			{{ $t("field.object.empty") }}
		</k-empty>
	</k-section>
</template>

<script>
export default {
	data() {
		return {
			days: []
		}
	},
	created() {
		this.load();
	},
	methods: {
		load() {
			this.$api
				.get(this.parent + "/sections/" + this.name)
				.then(response => {
					this.days = response.days
				})
		}
	}
};
</script>

<style lang="scss">
	.k-mrflix-protocol {
		&-empty {
			margin: 0 1.4em 2em;
			padding: .33em 0;
		}

		&-day {
			margin: 0 1.4em 2em;

			&-headline {
				padding: .33em 0;
				margin: -.33em 0;
				background: var(--color-background);
				position: sticky;
				top: 0;
				z-index: 1;
			}
		}
			
		&-entry {
			margin-top: 1em;
			display: flex;
			
			&-meta {
				display: flex;
				margin-right: .5em;
				
				&-time {
					font-size: .75em;
					margin-right: .8em;
					margin-top: 1px;
					color: var(--color-text-light);
					font-variant-numeric: tabular-nums;
				}
			}
			
			.k-icon {
				margin-top: 1px;
				display: inline-block;
				vertical-align: text-top;
			}

			a {
				text-decoration: underline;
			}

			&-detail {
				margin: .3em 0 0;
				font-size: .875em;
				color: var(--color-text-light);
			}
		}
	}
</style>
