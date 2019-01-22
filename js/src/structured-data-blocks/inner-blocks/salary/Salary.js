/* External dependencies */
import { Component, Fragment } from "react";
import { __ } from "@wordpress/i18n";

/* Internal dependencies */
import PropTypes from "prop-types";

/**
 * A Salary block component.
 */
export default class Salary extends Component {
	/**
	 * Constructs a Step block component instance.
	 *
	 * @param {Object} props Props for the React component.
	 *
	 * @returns {void}
	 */
	constructor( props ) {
		super( props );

		this.handleChangeSalary = this.handleChangeSalary.bind( this );
		this.handleChangeUnitText = this.handleChangeUnitText.bind( this );
		this.handleChangeCurrency = this.handleChangeCurrency.bind( this );
	}

	/**
     * Returns a preset for an unitText array.
     *
     * @param   {string} unitTextKey   The value of a unitText according to schema.org jobPosting (e.g. "hour", "day", etc.).
     * @param   {string} unitTextValue The displayed name of a front end unitText (e.g. "an hour", "a day", etc.).
     *
     * @returns {{unitTextKey: *, unitTextValue: *}} Preset for an unitText array.
     */
	static createUnitText( unitTextKey, unitTextValue ) {
		return {
			unitTextKey,
			unitTextValue,
		};
	}

	/**
     * Returns an array created with the createUnitText preset.
     *
     * @returns {{unitTextKey: *, unitTextValue: *}[]} Array created with the createUnitText preset.
     */
	static createUnitTextArray() {
		return [
			Salary.createUnitText( "HOUR",  __( "an hour", "yoast-jobs" ) ),
			Salary.createUnitText( "DAY",   __( "a day", "yoast-jobs" ) ),
			Salary.createUnitText( "WEEK",  __( "a week", "yoast-jobs" ) ),
			Salary.createUnitText( "MONTH", __( "a month", "yoast-jobs" ) ),
			Salary.createUnitText( "YEAR",  __( "a year", "yoast-jobs" ) ),
		];
	}

	/**
	 * Handles a change event on the salary input.
	 *
	 * @param {Event} event The fired change event.
	 *
	 * @returns {void}
	 */
	handleChangeSalary( event ) {
		const value = event.target.value;

		this.props.setAttributes( { salary: value } );
	}

	/**
	 * Handles a change event on the unitText input.
	 *
	 * @param {Event} event The fired change event.
	 *
	 * @returns {void}
	 */
	handleChangeUnitText( event ) {
		const value = event.target.value;

		this.props.setAttributes( { unitTextFrontEnd: value } );
	}

	/**
	 * Handles a change event on the currency input.
	 *
	 * @param {Event} event The fired change event.
	 *
	 * @returns {void}
	 */
	handleChangeCurrency( event ) {
		const value = event.target.value;

		this.props.setAttributes( { currency: value } );
	}

	/**
	 * Renders the front end content for the duration block.
	 *
	 * @param {Object} attributes The attributes for the Duration block.
	 *
	 * @returns {ReactElement} The rendered HTML for the front end.
	 */
	static Content( { attributes } ) {
		const attributesArrayRender = [
			attributes.salary,
			attributes.currency,
			attributes.unitTextFrontEnd,
		];

		return (
			<Fragment>
				<p>
					{ attributesArrayRender.map( attributeData => {
						if ( attributeData ) {
							return <span>{ attributeData } </span>;
						}
					} ) }
				</p>
			</Fragment>
		);
	}

	/**
     * Renders the editing for the duration block.
     *
	 * @returns {ReactElement} The rendered edit UI.
     */
	render() {
		const { setAttributes, attributes } = this.props;

		const unitTextArray = Salary.createUnitTextArray();

		const currencyArray = [
			"EUR",
			"USD",
			"GBP",
		];

		if ( attributes.unitTextFrontEnd === null ) {
			setAttributes( { unitTextFrontEnd: "an hour" } );
		}

		if ( attributes.currency === null ) {
			setAttributes( { currency: "EUR" } );
		}

		return (
			<fieldset className="schema-jobs-salary">
				<input
					value={ attributes.salary }
					onChange={ this.handleChangeSalary }
					type="number"
					step="500"
					placeholder="'3500.00'"
				/>
				<select onChange={ this.handleChangeCurrency }>
					{ currencyArray.map( currencyData => {
						return (
							<option
								selected={ currencyData === attributes.currency }
								key={ currencyData }
								value={ currencyData }
							>
								{ currencyData }
							</option>
						);
					} ) }
				</select>
				<select onChange={ this.handleChangeUnitText }>
					{ unitTextArray.map( unitTextData => {
						const {
							unitTextKey,
							unitTextValue,
						} = unitTextData;
						if ( attributes.unitTextFrontEnd === unitTextValue ) {
							setAttributes( { unitText: unitTextKey } );
						}

						return (
							<option
								selected={ unitTextValue === attributes.unitTextFrontEnd }
								key={ unitTextKey }
								value={ unitTextValue }
							>
								{ unitTextValue }
							</option>
						);
					} ) }
				</select>
			</fieldset>
		);
	}
}

Salary.propTypes = {
	attributes: PropTypes.object.isRequired,
	setAttributes: PropTypes.func.isRequired,
};

