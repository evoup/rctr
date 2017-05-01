#predict ctr
#dataset download https://pan.baidu.com/s/1mhQyFuK

mydir = "/home/evoup/project/rctr/"
inst_pkgs = load_pkgs =  c("ff","ffbase","biglm")
inst_pkgs = inst_pkgs[!(inst_pkgs %in% installed.packages()[,"Package"])]
if(length(inst_pkgs)) install.packages(inst_pkgs)
pkgs_loaded = lapply(load_pkgs, require, character.only=T)
setwd(mydir)

getOption("fftempdir")
options(fftempdir = paste(mydir,"temp",sep=""))

#click log column name
column_names <-c("BidID", "Timestamp", "LogType", "iPinYouID", "UserAgent", "IP", "Region", "City", "AdExchange", "Domain", "URL", 
"AnonymousURLID", "AdSlotID", "AdSlotWidth", "AdSlotHeight", "AdSlotVisibility", "AdSlotFormat", "AdSlotFloorPrice", "CreativeID",
"BiddingPrice", "PayingPrice", "KeyPageURL", "AdvertiserID", "UserTags", "isClicked")



training.data.raw <- read.table.ffdf(file=paste(mydir,"imp.20130606.txt.new",sep=""), VERBOSE=TRUE, header=FALSE, sep="\t",colClasses=NA, na.strings=c("null"))

names(training.data.raw)= column_names

test.data.raw <- read.table.ffdf(file=paste(mydir,"imp.20130607.txt.new",sep=""), VERBOSE=TRUE, header=FALSE, sep="\t",colClasses=NA, na.strings=c("null"))
names(test.data.raw)= column_names

#sapply(training.data.raw,function(x) sum(is.na(x)))
#sapply(training.data.raw, function(x) length(unique(x)))
#library(Amelia)
#par(mar = rep(2, 4))
#missmap(training.data.raw, main = "Missing values vs observed")

data <- subset(as.data.frame(training.data.raw),select=c(2,5,7,8,9,14,15,16,17,18,20,21,23,24,25))
data_test <- subset(as.data.frame(test.data.raw),select=c(2,5,7,8,9,14,15,16,17,18,20,21,23,24,25))

data_test_clicked = data[data$isClicked == 1, ]
data_test_noclicked = data_test[data_test$isClicked == 0, ]




model <- glm(isClicked ~.,family=binomial(link='logit'),data=data)


summary(model)  

data <- subset(as.data.frame(training.data.raw),select=c(2,5,9,17,18,20,21,23,24,25))
data_test <- subset(as.data.frame(test.data.raw),select=c(2,5,9,17,18,20,21,23,24,25))
model_reduce <- glm(isClicked ~.,family=binomial(link='logit'),data=data)
summary(model_reduce)

anova(model, model_reduce, test="Chisq")



#0.39% clicked=1
newdata = data.frame(Timestamp=0, UserAgent=1, Region=94, City=96,  AdExchange=1,AdSlotWidth=300,AdSlotHeight=250, AdSlotVisibility=0, AdSlotFormat = 1, 
                      AdSlotFloorPrice=0,BiddingPrice = 227,PayingPrice=168,AdvertiserID=3358, UserTags=3566309742, isClicked=1)
predict(model_reduce, newdata, type="response")

#0.04% clicked=0
newdata = data.frame(Timestamp=0, UserAgent=4, Region=65, City=75,  AdExchange=2,AdSlotWidth=728,AdSlotHeight=90, AdSlotVisibility=2, AdSlotFormat = 0, 
                     AdSlotFloorPrice=5,BiddingPrice = 238,PayingPrice=236,AdvertiserID=3427, UserTags=3839198970, isClicked=0)
predict(model_reduce, newdata, type="response")

#0.04% clicked=0
newdata = data.frame(Timestamp=8, UserAgent=4, Region=94, City=95,  AdExchange=1,AdSlotWidth=950,AdSlotHeight=90, AdSlotVisibility=1, AdSlotFormat = 1, 
                     AdSlotFloorPrice=0,BiddingPrice = 227,PayingPrice=218,AdvertiserID=3427, UserTags=2739350591, isClicked=0)
predict(model_reduce, newdata, type="response")


fitted.results <- predict(model_reduce,newdata=subset(data_test_clicked),type='response')
fitted.results <- ifelse(fitted.results > 0.000387,1,0)

misClasificError <- mean(fitted.results != data_test_clicked$isClicked)
print(paste('Accuracy',1-misClasificError))


