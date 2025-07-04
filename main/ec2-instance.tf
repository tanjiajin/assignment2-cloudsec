# CloudFront CDN (Optional)
#resource "aws_cloudfront_distribution" "cf" {
#  origin {
#    domain_name = aws_s3_bucket.static_assets.bucket_regional_domain_name
#    origin_id   = "S3-static-assets"
#  }
#
#  enabled             = true
#  is_ipv6_enabled     = true
#  default_root_object = "index.html"
#}