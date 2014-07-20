<?php
/*
 * @author Alexandr Viniychuk(alexandr@vinihychuk.com)
 */
//__INDEX_INIT__
SL::loadApplication(slRouter::getInstance()->detectApplication())->run();