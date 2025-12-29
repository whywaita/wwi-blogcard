/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import {
	Placeholder,
	TextControl,
	Button,
	Spinner,
	Notice,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { link } from '@wordpress/icons';
import apiFetch from '@wordpress/api-fetch';

/**
 * Edit component for the Blogcard block
 *
 * @param {Object}   props               Component props
 * @param {Object}   props.attributes    Block attributes
 * @param {Function} props.setAttributes Function to update attributes
 * @return {JSX.Element} Block editor output
 */
export default function Edit( { attributes, setAttributes } ) {
	const { url, title, description, image, siteName, favicon } = attributes;

	const [ inputUrl, setInputUrl ] = useState( url );
	const [ isLoading, setIsLoading ] = useState( false );
	const [ error, setError ] = useState( '' );

	const blockProps = useBlockProps();

	/**
	 * Fetch OGP data from the REST API
	 */
	const fetchOgpData = async () => {
		if ( ! inputUrl ) {
			setError( __( 'Please enter a URL.', 'wwi-blogcard' ) );
			return;
		}

		setIsLoading( true );
		setError( '' );

		try {
			const response = await apiFetch( {
				path: '/wwi-blogcard/v1/fetch',
				method: 'POST',
				data: { url: inputUrl },
			} );

			if ( response.success && response.data ) {
				setAttributes( {
					url: inputUrl,
					title: response.data.title || '',
					description: response.data.description || '',
					image: response.data.image || '',
					siteName: response.data.site_name || '',
					favicon: response.data.favicon || '',
				} );
			} else {
				setError(
					response.message ||
						__( 'Failed to fetch OGP data.', 'wwi-blogcard' )
				);
			}
		} catch ( err ) {
			setError(
				err.message || __( 'Failed to fetch OGP data.', 'wwi-blogcard' )
			);
		} finally {
			setIsLoading( false );
		}
	};

	/**
	 * Reset the block to initial state
	 */
	const resetBlock = () => {
		setAttributes( {
			url: '',
			title: '',
			description: '',
			image: '',
			siteName: '',
			favicon: '',
		} );
		setInputUrl( '' );
		setError( '' );
	};

	// Show placeholder if no URL is set
	if ( ! url || ! title ) {
		return (
			<div { ...blockProps }>
				{ error && (
					<Notice status="error" isDismissible={ false }>
						{ error }
					</Notice>
				) }
				<Placeholder
					icon={ link }
					label={ __( 'Blogcard', 'wwi-blogcard' ) }
					instructions={ __(
						'Enter a URL to create a blog card.',
						'wwi-blogcard'
					) }
				>
					<div className="wwi-blogcard-editor__input-wrapper">
						<TextControl
							__nextHasNoMarginBottom
							label={ __( 'URL', 'wwi-blogcard' ) }
							hideLabelFromVision
							value={ inputUrl }
							onChange={ setInputUrl }
							placeholder="https://example.com"
							type="url"
						/>
						<Button
							variant="primary"
							onClick={ fetchOgpData }
							disabled={ isLoading }
						>
							{ isLoading && <Spinner /> }
							{ __( 'Fetch', 'wwi-blogcard' ) }
						</Button>
					</div>
				</Placeholder>
			</div>
		);
	}

	// Show preview
	return (
		<div { ...blockProps }>
			<div className="wwi-blogcard">
				<a href={ url } target="_blank" rel="noopener noreferrer">
					<div className="wwi-blogcard__title">{ title }</div>
					<div className="wwi-blogcard__body">
						<div className="wwi-blogcard__content">
							{ description && (
								<div className="wwi-blogcard__description">
									{ description }
								</div>
							) }
							<div className="wwi-blogcard__meta">
								{ favicon && (
									<img
										className="wwi-blogcard__favicon"
										src={ favicon }
										alt=""
									/>
								) }
								<span className="wwi-blogcard__site-name">
									{ siteName }
								</span>
							</div>
						</div>
						{ image && (
							<div className="wwi-blogcard__image">
								<img src={ image } alt={ title } />
							</div>
						) }
					</div>
				</a>
			</div>
			<div className="wwi-blogcard-editor__actions">
				<Button variant="secondary" onClick={ resetBlock }>
					{ __( 'Change URL', 'wwi-blogcard' ) }
				</Button>
			</div>
		</div>
	);
}
