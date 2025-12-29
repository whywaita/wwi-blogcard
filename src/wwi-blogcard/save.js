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
			<div className="wp-blogcard">
				<a href={ url } target="_blank" rel="noopener noreferrer">
					<div className="wp-blogcard__title">{ title }</div>
					<div className="wp-blogcard__body">
						<div className="wp-blogcard__content">
							{ description && (
								<div className="wp-blogcard__description">
									{ description }
								</div>
							) }
							<div className="wp-blogcard__meta">
								{ favicon && (
									<img
										className="wp-blogcard__favicon"
										src={ favicon }
										alt=""
									/>
								) }
								<span className="wp-blogcard__site-name">
									{ siteName }
								</span>
							</div>
						</div>
						{ image && (
							<div className="wp-blogcard__image">
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
