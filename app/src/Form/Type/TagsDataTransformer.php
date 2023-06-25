<?php
/**
 * Tags data transformer.
 */

namespace App\Form\Type;

use App\Entity\Tags;
use App\Service\TagsServiceInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class TagsDataTransformer.
 *
 * @implements DataTransformerInterface<mixed, mixed>
 */
class TagsDataTransformer implements DataTransformerInterface
{
    /**
     * Tag service.
     */
    private TagsServiceInterface $tagsService;

    /**
     * Constructor.
     *
     * @param TagsServiceInterface $tagsService Tag service
     */
    public function __construct(TagsServiceInterface $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    /**
     * Transform array of tags to string of tag names.
     *
     * @param Collection<int, Tag> $value Tags entity collection
     *
     * @return string Result
     */
    public function transform($value): string
    {
        if ($value->isEmpty()) {
            return '';
        }

        $tagNames = [];

        foreach ($value as $tag) {
            $tagNames[] = $tag->getName();
        }

        return implode(', ', $tagNames);
    }

    /**
     * Transform string of tag names into array of Tag entities.
     *
     * @param string $value String of tag names
     *
     * @return array<int, Tag> Result
     */
    public function reverseTransform($value): array
    {
        $tagNames = explode(',', $value);

        $tags = [];

        foreach ($tagNames as $tagNames) {
            if ('' !== trim($tagNames)) {
                $tag = $this->tagsService->findOneByName(strtolower($tagNames));
                if (null === $tag) {
                    $tag = new Tags();
                    $tag->setName($tagNames);

                    $this->tagsService->save($tag);
                }
                $tags[] = $tag;
            }
        }

        return $tags;
    }
}
