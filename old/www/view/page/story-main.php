<style>
:root{

}
.story__container{
    display:grid;
    grid-template-columns: 50% 50%;
    height: calc(100vh - 50px);
}
.story__item{
    position:relative;
}
.story__item img{
    object-fit:cover;
    height:100%;
    width:100%;
    opacity: 0.5;
}
.story__item:hover img{
    opacity: 1;
}
.story__item p{
    font-size: 12px;
    position:absolute;
    bottom:20px;
    left:20px;
}
.story__moblie__container{
    display:grid;
    grid-template-columns: 51.39% 48.61%;
    grid-template-rows: auto 54.5px auto 54.5px;
}
.story__moblie__item{ 
    border-right: 1px solid #dcdcdc;
    border-bottom: 1px solid #dcdcdc;
}
.story__moblie__item img{ width:100%; }
.story__moblie__item.left_area{
    padding-top:10px;
    padding-left:10px;
    font-size: 1.2rem;
}
.story__moblie__item.right_area{
    padding-top:10px;
    padding-left:5px;
    font-size: 1.2rem;
}

@media (min-width: 1024px){
    .story__container{display:grid;}
    .story__moblie__container{display:none;}
}
@media (max-width: 1024px){
    .story__container{display:none;}
    .story__moblie__container{display:grid;}
}
</style>
<main>
    <div class="story__container">
        <div class="story__item" seq="1">
            <a href="/posting/editorial">
                <img src="/images/story/Editorial.jpg" alt="editorial">
                <p>Editorial</p>
            </a>
        </div>
        <!--    임시로 가려둠 완전 삭제는 아님
        <div class="story__item" seq="2">
            <a href="/posting/runway">
                <img src="/images/story/Runway.jpg" alt="runway">
                <p>Runway</p>
            </a>
        </div>
        -->
        <div class="story__item" seq="3">
            <a href="/posting/collection">
                <img src="/images/story/Collection.jpg" alt="collection">
                <p>Collection</p>
            </a>
        </div>
        <!--    임시로 가려둠 완전 삭제는 아님
        <div class="story__item" seq="4">
            <a href="/posting/collaboration">
                <img src="/images/story/Collaboration.jpg" alt="collaboration">
                <p>Collaboration</p>
            </a>
        </div>
        -->
    </div>

    <div class="story__moblie__container">
        <div class="story__moblie__item">
            <a href="/posting/editorial">
                <img src="/images/story/moblie_editorial_story.png" alt="editorial">
            </a>
        </div>
        <!--    임시로 가려둠 완전 삭제는 아님
        <div class="story__moblie__item">
        <a href="/posting/runway">
            <img src="/images/story/moblie_runway_story.png" alt="runway">
        </a>
        -->
        <div class="story__moblie__item">
            <a href="/posting/collection">
                <img src="/images/story/moblie_collection_story.png" alt="collection">
            </a>
        </div>
        <div class="story__moblie__item left_area">
            <div>Editorial</div>
        </div>
        <!--    임시로 가려둠 완전 삭제는 아님
        <div class="story__moblie__item right_area">
            <div>Runway</div>
        </div>
        -->
        
        <!--    임시로 가려둠 완전 삭제는 아님
        <div class="story__moblie__item">
            <a href="/posting/collaboration">
                <img src="/images/story/moblie_collaboration_story.png" alt="collaboration">
            </a>
        </div>
        -->
        <div class="story__moblie__item left_area">
            <div>Collection</div>
        </div>
        <!--    임시로 가려둠 완전 삭제는 아님
        <div class="story__moblie__item right_area">
            <div>Collaboration</div>
        </div>
        -->
    </div>
</main>
