/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Save component for the Blogcard block
 *
 * @param {Object} props            Component props
 * @param {Object} props.attributes Block attributes
 * @return {JSX.Element|null} Block save output
 */
export default function save( { attributes } ) {
	const { url, title, description, image, siteName, favicon } = attributes;

	// Don't render if no URL
	if ( ! url || ! title ) {
		return null;
	}

	const blockProps = useBlockProps.save();

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
								<img
									src={ image }
									alt={ title }
									loading="lazy"
								/>
							</div>
						) }
					</div>
				</a>
			</div>
		</div>
	);
}
